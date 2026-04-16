<?php

namespace App\Services;

use App\Models\Number;
use App\Models\NumberStat;
use Carbon\Carbon;

class NumberStatsService
{
  public function getMostFrequentNumbers($region, $day)
  {
    $column = ($day && $day != 0)
      ? "total_count_{$day}_days"
      : "total_count";

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
