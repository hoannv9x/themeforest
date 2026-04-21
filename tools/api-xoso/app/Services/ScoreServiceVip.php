<?php

namespace App\Services;

use App\Services\Commons\ScoreServiceBase;

class ScoreServiceVip extends ScoreServiceBase
{
  protected array $weights = [
    'gap' => 0.30,
    'db_gap' => 0.25,
    'freq' => 0.15,
    'cooldown' => 0.10,
    'trend' => 0.20,
    'mean_reversion' => 0.15,
    'mean_reversion_db' => 0.10,
  ];

  protected function extraScore($item, $minMax): float
  {
    $trend = $this->calculateTrendScore($item);
    $mr = $this->calculateMeanReversionScore($item);

    return
      $this->weights['trend'] * $trend +
      $this->weights['mean_reversion'] * $mr;
  }

  protected function calculateTrendScore($item): float
  {
    $short = $item->total_count_7_days;
    $mid = max($item->total_count_30_days / 4, 1);
    $long = max($item->total_count_90_days / 3, 1);

    $trend =
      0.6 * ($short / $mid) +
      0.4 * ($item->total_count_30_days / $long);

    if ($item->total_count_30_days < 5) {
      $trend *= 0.5;
    }

    return min($trend, 1.5);
  }

  // ===== MEAN REVERSION =====
  protected function calculateMeanReversionScore($item): float
  {
    if ($item->std_gap == 0 || $item->avg_gap == 0) {
      return 0;
    }

    // Z-score
    $z = ($item->current_gap - $item->avg_gap) / $item->std_gap;

    // sigmoid
    $score = 1 / (1 + exp(-$z));

    // ❗ clamp nhẹ để tránh quá đà
    return min($score, 0.95);
  }

  protected function calculateMeanReversionDbScore($item): float
  {
    if ($item->std_gap_db == 0 || $item->avg_gap_db == 0) {
      return 0;
    }

    $z = ($item->current_gap_db - $item->avg_gap_db) / $item->std_gap_db;

    $score = 1 / (1 + exp(-$z));

    return min($score, 0.95);
  }

  // ===== RULES OVERRIDE =====
  protected function applyRules($item, float $score, float $gapScore): float
  {
    $score = parent::applyRules($item, $score, $gapScore);

    // 🔥 vùng đẹp (mean reversion)
    if ($item->std_gap > 0) {
      $z = ($item->current_gap - $item->avg_gap) / $item->std_gap;

      // sweet spot
      if ($z > 1.5 && $z < 2.5) {
        $score *= 1.15;
      }

      // quá lệch → nguy hiểm
      if ($z > 3) {
        $score *= 0.8;
      }
    }

    return $score;
  }

  public function setWeights(array $weights): self
  {
    $this->weights = $weights;
    return $this;
  }
}
