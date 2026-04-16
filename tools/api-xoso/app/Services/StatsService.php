<?php

namespace App\Services;

use App\Models\Number;
use App\Models\NumberStat;
use Carbon\Carbon;

class StatsService
{
  public function calculate()
  {
    $numbers = Number::all();

    $grouped = $numbers->groupBy('number');

    foreach ($grouped as $number => $items) {
      $last = $items->sortByDesc('created_at')->first();

      NumberStat::updateOrCreate(
        ['number' => $number, 'region' => $items->first()->region],
        [
          'total_count' => $items->count(),
          'last_appeared_at' => $last->created_at,
          'current_gap' => now()->diffInDays($last->created_at)
        ]
      );
    }
  }
}
