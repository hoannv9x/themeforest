<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Number;
use App\Models\NumberStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function vipIndex(Request $request)
    {
        $region = $request->query('region', Number::REGION_MB);
        return response()->json(
            NumberStat::query()
                ->where('region', $region)
                ->orderByDesc('current_gap')
                ->get()
        );
    }

    public function vipDetail(Request $request, int $number)
    {
        $region = $request->query('region', Number::REGION_MB);
        $num = str_pad((string) $number, 2, '0', STR_PAD_LEFT);
        return response()->json(
            NumberStat::query()->where('region', $region)->where('number', $num)->first()
        );
    }

    public function index(Request $request)
    {
        $region = $request->query('region', Number::REGION_MB);

        $now = Carbon::now(config('app.timezone'))->startOfDay();
        $from = $now->copy()->subYears(2)->toDateString();

        $rows = DB::table('numbers')
            ->join('results', 'numbers.result_id', '=', 'results.id')
            ->where('results.region', $region)
            ->whereDate('results.date', '>=', $from)
            ->groupBy('numbers.number')
            ->selectRaw('numbers.number as number, COUNT(*) as total_count, MAX(results.date) as last_appeared_at')
            ->get()
            ->keyBy(function ($r) {
                $num = str_pad((string) $r->number, 2, '0', STR_PAD_LEFT);
                return $num;
            });

        $fallbackGap = Carbon::parse($from)->diffInDays($now) + 1;
        $out = [];
        for ($i = 0; $i <= 99; $i++) {
            $num = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $r = $rows->get($num);
            $last = $r?->last_appeared_at ? Carbon::parse($r->last_appeared_at)->format('Y-m-d') : null;
            $gap = $last ? Carbon::parse($last)->diffInDays($now) : $fallbackGap;
            $out[] = [
                'number' => $num,
                'region' => $region,
                'total_count' => (int) ($r?->total_count ?? 0),
                'last_appeared_at' => $last,
                'current_gap' => $gap,
                'max_gap' => $gap,
            ];
        }

        usort($out, fn ($a, $b) => ((int) $b['current_gap']) <=> ((int) $a['current_gap']));
        return response()->json($out);
    }

    public function detail(Request $request, int $number)
    {
        $region = $request->query('region', Number::REGION_MB);
        $num = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

        $now = Carbon::now(config('app.timezone'))->startOfDay();
        $from = $now->copy()->subYears(2)->toDateString();

        $row = DB::table('numbers')
            ->join('results', 'numbers.result_id', '=', 'results.id')
            ->where('results.region', $region)
            ->whereDate('results.date', '>=', $from)
            ->where('numbers.number', $num)
            ->selectRaw('COUNT(*) as total_count, MAX(results.date) as last_appeared_at')
            ->first();

        $last = $row?->last_appeared_at ? Carbon::parse($row->last_appeared_at)->format('Y-m-d') : null;
        $fallbackGap = Carbon::parse($from)->diffInDays($now) + 1;
        $gap = $last ? Carbon::parse($last)->diffInDays($now) : $fallbackGap;

        return response()->json([
            'number' => $num,
            'region' => $region,
            'total_count' => (int) ($row?->total_count ?? 0),
            'last_appeared_at' => $last,
            'current_gap' => $gap,
            'max_gap' => $gap,
        ]);
    }
}
