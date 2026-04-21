<?php

namespace App\Pipelines;

use App\Pipelines\Steps\CalculateStats;
use App\Pipelines\Steps\GroupData;
use App\Pipelines\Steps\LoadData;
use App\Pipelines\Steps\PersistStats;
use App\Pipelines\Steps\UpdatePredictionAccuracy;
use Illuminate\Pipeline\Pipeline;

class StatsPipeline
{
  public function handle(string $region)
  {
    app(Pipeline::class)
      ->send([
        'region' => $region,
      ])
      ->through([
        LoadData::class,
        GroupData::class,
        CalculateStats::class,
        PersistStats::class,
        UpdatePredictionAccuracy::class,
      ])
      ->thenReturn();
  }
}
