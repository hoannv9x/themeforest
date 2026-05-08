<?php

namespace App\Services;

use App\Models\Number;
use App\Models\NumberStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NumberStatsService
{
  public function getMostFrequentNumbers($region, $day, ?int $scopeDays = null)
  {
    if ($scopeDays !== null) {
      $scopeDays = min(max($scopeDays, 1), 730);
      $now = Carbon::now(config('app.timezone'))->startOfDay();
      $from = $now->copy()->subDays($scopeDays)->toDateString();

      $windowDays = ($day && $day != 0) ? (int) $day : $scopeDays;
      $windowDays = min($windowDays, $scopeDays);
      $windowFrom = $now->copy()->subDays($windowDays)->toDateString();

      $rows = DB::table('numbers')
        ->join('results', 'numbers.result_id', '=', 'results.id')
        ->where('results.region', $region)
        ->whereDate('results.date', '>=', $from)
        ->whereDate('results.date', '>=', $windowFrom)
        ->groupBy('numbers.number')
        ->selectRaw('numbers.number as number, COUNT(*) as total_count')
        ->orderByDesc('total_count')
        ->limit(10)
        ->get();

      return collect($rows)->map(function ($r) {
        $num = str_pad((string) $r->number, 2, '0', STR_PAD_LEFT);
        return [
          'number' => $num,
          'score' => (int) ($r->total_count ?? 0),
        ];
      })->values();
    }

    $column = ($day && $day != 0) ? "total_count_{$day}_days" : "total_count";

    return NumberStat::where('region', $region)
      ->orderBy($column, 'desc')
      ->take(10)
      ->get()
      ->map(fn($n) => [
        'number' => $n->number,
        'score' => $n->{$column},
      ]);
  }
}
