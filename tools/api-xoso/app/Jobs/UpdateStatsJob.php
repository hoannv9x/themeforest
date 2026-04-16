<?php

namespace App\Jobs;

use App\Models\Number;
use App\Models\NumberStat;
use App\Models\Prediction;
use App\Models\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateStatsJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;

    public function handle()
    {
        $regions = Number::REGIONS;
        $days = [7, 30, 90, 180, 365];
        foreach ($regions as $key => $region) {
            try {
                DB::beginTransaction();
                // 🗓 lấy tất cả ngày (unique)
                $latestDate = Number::join('results', 'numbers.result_id', '=', 'results.id')
                    ->where('results.region', $region)
                    ->max('results.date');

                // Calculate the date 90 days ago from the latest date
                // $cutoffDate = Carbon::parse($latestDate)->subDays(90)->format('Y-m-d');
                $cutoffDate = '2025-01-01';

                // 🔢 loop từ 00 → 99
                for ($i = 0; $i <= 99; $i++) {
                    $totalCountByDays = [];

                    $num = str_pad($i, 2, '0', STR_PAD_LEFT);

                    // 📊 total_count within last 90 days
                    $totalCount = Number::where('number', $num)
                        ->whereNotIn('prize', ['ma_db', 'db'])
                        ->whereHas('result', function ($query) use ($cutoffDate) {
                            $query->where('date', '>=', $cutoffDate);
                        })
                        ->count();
                    // 📅 last appeared within last 90 days
                    $last = Number::query()
                        ->join('results', 'results.id', '=', 'numbers.result_id')
                        ->where('numbers.number', $num)
                        ->whereNotIn('numbers.prize', ['ma_db', 'db'])
                        ->where('results.date', '>=', $cutoffDate)
                        ->where('results.region', $region)
                        ->orderByDesc('results.date')
                        ->select('results.date')
                        ->first();

                    $lastDate = $last ? $last->date : null;
                    // 🔥 current_gap
                    if ($lastDate) {
                        $currentGap = $lastDate
                            ? Carbon::parse($lastDate)->diffInDays($latestDate)
                            : null;
                    } else {
                        $currentGap = -1;
                    }

                    foreach ($days as $key => $value) {
                        $cutoffDate = Carbon::parse($latestDate)->subDays($value)->format('Y-m-d');
                        $totalCountByDays[$value] = Number::where('number', $num)
                        ->whereNotIn('prize', ['ma_db', 'db'])
                        ->whereHas('result', function ($query) use ($cutoffDate) {
                            $query->where('date', '>=', $cutoffDate);
                        })
                        ->count();
                    }

                    // 💾 update / insert
                    NumberStat::updateOrCreate(
                        ['number' => $num, 'region' => $region],
                        [
                            'total_count' => $totalCount,
                            'total_count_7_days' => $totalCountByDays[7] ?? 0,
                            'total_count_30_days' => $totalCountByDays[30] ?? 0,
                            'total_count_90_days' => $totalCountByDays[90] ?? 0,
                            'total_count_180_days' => $totalCountByDays[180] ?? 0,
                            'total_count_365_days' => $totalCountByDays[365] ?? 0,
                            'last_appeared_at' => $lastDate,
                            'current_gap' => $currentGap ?? 0,
                            'updated_at' => now()
                        ]
                    );
                }

                $resultYesterday = Result::where('date', $latestDate)->where('region', $region)->first();

                if ($resultYesterday) {
                    $numberYesterday = Number::where('result_id', $resultYesterday->id)
                        ->whereNotIn('prize', ['ma_db', 'db'])
                        ->select('number')
                        ->get()->toArray();

                    $numberYesterday = array_column($numberYesterday, 'number');
                    $today = Carbon::parse($latestDate)->addDay()->toDateString();
                    $predictionToday = Prediction::where('date', $today)->first();
                    if ($predictionToday) {
                        $numberPrediction = array_column($predictionToday->numbers, 'number');
                        $count = count(array_intersect($numberPrediction, $numberYesterday));

                        $predictionToday->accuracy = $count / count($numberPrediction);
                        $predictionToday->save();
                    }
                }
                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();
                Log::error('UpdateStatsJob error region: ' . $region . ': ' . $e);
            }
        }
    }
}
