<?php

namespace App\Pipelines\Steps;

use Illuminate\Support\Carbon;

class CalculateStats
{
  public function handle($payload, $next)
  {
    $latestDate = Carbon::parse($payload['latestDate']);
    $days = [7, 30, 90, 180, 365];
    $rows = [];

    foreach (range(0, 99) as $n) {
      $num = str_pad($n, 2, '0', STR_PAD_LEFT);

      $dates = collect($payload['normal'][$num] ?? [])
        ->map(fn($d) => Carbon::parse($d));

      $dbDates = collect($payload['db'][$num] ?? [])
        ->map(fn($d) => Carbon::parse($d));

      // current gap
      $last = $dates->last();
      $lastAppearedAt = $last?->toDateString();
      $currentGap = $last ? $last->diffInDays($latestDate) : -1;

      // max gap
      $maxGap = 0;
      for ($i = 1; $i < $dates->count(); $i++) {
        $maxGap = max($maxGap, $dates[$i - 1]->diffInDays($dates[$i]));
      }

      // DB
      $lastDb = $dbDates->last();
      $currentGapDb = $lastDb ? $lastDb->diffInDays($latestDate) : -1;

      $maxGapDb = 0;
      for ($i = 1; $i < $dbDates->count(); $i++) {
        $maxGapDb = max($maxGapDb, $dbDates[$i - 1]->diffInDays($dbDates[$i]));
      }

      // 📊 total_count
      $totalCount = $dates->count();

      // 📊 total_count_by_days
      $countsByDays = [];

      foreach ($days as $day) {
        $cutoff = $latestDate->copy()->subDays($day);

        $countsByDays[$day] = $dates->filter(
          fn($d) => $d->gte($cutoff)
        )->count();
      }

      // 📊 avg_gap
      $gaps = [];
      for ($i = 1; $i < $dates->count(); $i++) {
        $gaps[] = $dates[$i - 1]->diffInDays($dates[$i]);
      }
      $avgGap = count($gaps) ? array_sum($gaps) / count($gaps) : 0;

      // std
      $variance = 0;
      foreach ($gaps as $g) {
        $variance += pow($g - $avgGap, 2);
      }
      $stdGap = count($gaps) ? sqrt($variance / count($gaps)) : 0;

      // DB gaps
      $dbGaps = [];
      for ($i = 1; $i < $dbDates->count(); $i++) {
        $dbGaps[] = $dbDates[$i - 1]->diffInDays($dbDates[$i]);
      }
      $avgGapDb = count($dbGaps) ? array_sum($dbGaps) / count($dbGaps) : 0;
      $varianceDb = 0;
      foreach ($dbGaps as $g) {
        $varianceDb += pow($g - $avgGapDb, 2);
      }
      $stdGapDb = count($dbGaps) ? sqrt($varianceDb / count($dbGaps)) : 0;

      $rows[] = [
        'number' => $num,
        'region' => $payload['region'],

        // 📊 frequency
        'total_count' => $totalCount,
        'total_count_7_days' => $countsByDays[7] ?? 0,
        'total_count_30_days' => $countsByDays[30] ?? 0,
        'total_count_90_days' => $countsByDays[90] ?? 0,
        'total_count_180_days' => $countsByDays[180] ?? 0,
        'total_count_365_days' => $countsByDays[365] ?? 0,

        // 📅 time
        'last_appeared_at' => $lastAppearedAt,
        'last_appeared_at_db' => $lastDb?->toDateString() ?? null,

        // 🔥 gap
        'current_gap' => $currentGap,
        'max_gap' => $maxGap,

        // 💎 DB gap
        'current_gap_db' => $currentGapDb,
        'max_gap_db' => $maxGapDb,

        'avg_gap' => $avgGap,
        'std_gap' => $stdGap,

        'avg_gap_db' => $avgGapDb,
        'std_gap_db' => $stdGapDb,
        'updated_at' => now(),
      ];
    }

    $payload['rows'] = $rows;

    return $next($payload);
  }
}
