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
        try {

            DB::beginTransaction();
            $regions = Number::REGIONS;

            foreach ($regions as $key => $region) {

                // 🗓 lấy tất cả ngày (unique)
                $latestDate = Number::join('results', 'numbers.result_id', '=', 'results.id')
                    ->where('results.region', $region)
                    ->max('results.date');

                // Calculate the date 90 days ago from the latest date
                $cutoffDate = Carbon::parse($latestDate)->subDays(90);

                // 🔢 loop từ 00 → 99
                for ($i = 0; $i <= 99; $i++) {

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
                    $neverHit = 0;
                    // 🔥 current_gap
                    if ($lastDate) {
                        $currentGap = $lastDate
                            ? Carbon::parse($lastDate)->diffInDays($latestDate)
                            : null;
                    } else {
                        $currentGap = 999;
                        $neverHit = 1; // chưa từng ra
                    }

                    // 💾 update / insert
                    NumberStat::updateOrCreate(
                        ['number' => $num],
                        [
                            'total_count' => $totalCount,
                            'last_appeared_at' => $lastDate,
                            'current_gap' => $currentGap ?? 0,
                            'never_hit' => $neverHit,
                            'updated_at' => now(),
                            'last_appeared_at' => $lastDate,
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
            }

            DB::commit();
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('UpdateStatsJob error: ' . $e);
        }
    }
}
