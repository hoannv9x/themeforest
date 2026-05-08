<?php

namespace App\Pipelines\Steps;

use App\Models\NumberStat;

class PersistStats
{
  public function handle($payload, $next)
  {
    NumberStat::upsert(
      $payload['rows'],
      ['number', 'region'],
      [
        'total_count',
        'total_count_db',
        'total_count_7_days',
        'total_count_30_days',
        'total_count_90_days',
        'total_count_180_days',
        'total_count_365_days',
        'last_appeared_at',
        'last_appeared_at_db',
        'current_gap',
        'max_gap',
        'current_gap_db',
        'max_gap_db',
        'avg_gap',
        'std_gap',
        'avg_gap_db',
        'std_gap_db',
        'updated_at'
      ]
    );

    return $next($payload);
  }
}
