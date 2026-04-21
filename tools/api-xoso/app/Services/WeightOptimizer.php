<?php

namespace App\Services;

use App\Models\ModelWeight;
use Illuminate\Support\Collection;

class WeightOptimizer
{
  public function optimize(array $candidates, Collection $dataset, int $topN = 5): array
  {
    $bestScore = 0;
    $bestWeights = [];

    foreach ($candidates as $weights) {
      $score = $this->evaluate($weights, $dataset, $topN);

      if ($score > $bestScore) {
        $bestScore = $score;
        $bestWeights = $weights;
      }
    }

    // save to db
    ModelWeight::updateOrInsert(
      ['type' => 'vip'],
      ['weights' => json_encode($bestWeights)]
    );

    return [
      'weights' => $bestWeights,
      'score' => $bestScore,
    ];
  }

  protected function evaluate(array $weights, Collection $dataset, int $topN = 5): float
  {
    $totalHit = 0;
    $totalPick = 0;

    foreach ($dataset as $day) {
      $stats = $day['stats'];
      $result = $day['result'];

      $ranked = app(ScoreServiceVip::class)
        ->setWeights($weights)
        ->rank($stats)
        ->take($topN);

      $predicted = $ranked->pluck('number')->toArray();

      $hit = count(array_intersect($predicted, $result));

      $totalHit += $hit;
      $totalPick += count($predicted);
    }

    return $totalPick > 0 ? $totalHit / $totalPick : 0;
  }
}
