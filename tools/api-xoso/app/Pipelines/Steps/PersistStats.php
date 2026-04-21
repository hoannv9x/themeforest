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
        'current_gap',
        'max_gap',
        'current_gap_db',
        'max_gap_db',
        'updated_at'
      ]
    );

    return $next($payload);
  }
}
