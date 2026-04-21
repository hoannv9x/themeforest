<?php

namespace App\Services;

use App\Services\Commons\ScoreServiceBase;

class ScoreService extends ScoreServiceBase
{
  protected array $weights = [
    'gap' => 0.5,
    'db_gap' => 0.3,
    'freq' => 0.2,
    'cooldown' => 0.0,
  ];
}
