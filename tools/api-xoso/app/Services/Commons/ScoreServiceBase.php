<?php

namespace App\Services\Commons;

use Illuminate\Support\Collection;
use Carbon\Carbon;

abstract class ScoreServiceBase
{
  protected array $weights = [];

  public function rank(Collection $stats): Collection
  {
    if ($stats->isEmpty()) return collect();

    $minMax = $this->getMinMax($stats);

    return $stats->map(function ($item) use ($minMax) {
      $score = $this->calculateScore($item, $minMax);

      return [
        ...$item->toArray(),
        'score' => round($score, 4),
      ];
    })
      ->sortByDesc('score')
      ->values();
  }

  protected function calculateScore($item, $minMax): float
  {
    $gapScore = $this->calculateGapScore($item);
    $dbGapScore = $this->calculateDbGapScore($item);
    $freqScore = $this->calculateFreqScore($item, $minMax);
    $cooldownScore = $this->calculateCooldownScore($item);

    $score =
      $this->weights['gap'] * $gapScore +
      $this->weights['db_gap'] * $dbGapScore +
      $this->weights['freq'] * (1 - $freqScore) +
      $this->weights['cooldown'] * $cooldownScore;

    // 👇 hook cho VIP override
    $score += $this->extraScore($item, $minMax);

    return $this->applyRules($item, $score, $gapScore);
  }

  // ===== CORE PART =====

  protected function calculateGapScore($item): float
  {
    $ratio = $item->max_gap > 0 ? $item->current_gap / $item->max_gap : 0;
    return min($ratio, 1.2);
  }

  protected function calculateDbGapScore($item): float
  {
    $ratio = $item->max_gap_db > 0 ? $item->current_gap_db / $item->max_gap_db : 0;
    return min($ratio, 1.5);
  }

  protected function calculateFreqScore($item, $minMax): float
  {
    return
      0.5 * $this->normalize($item->total_count_7_days, $minMax['7']) +
      0.3 * $this->normalize($item->total_count_30_days, $minMax['30']) +
      0.2 * $this->normalize($item->total_count_90_days, $minMax['90']);
  }

  protected function calculateCooldownScore($item): float
  {
    $days = $item->last_appeared_at
      ? Carbon::parse($item->last_appeared_at)->diffInDays(now())
      : 999;

    return min($days / 10, 1);
  }

  // ===== EXTENSION POINT =====

  protected function extraScore($item, $minMax): float
  {
    return 0; // Free không có gì thêm
  }

  // ===== RULES =====

  protected function applyRules($item, float $score, float $gapScore): float
  {
    // Vừa về hôm gần nhất => xác suất lặp ngắn hạn thấp, phạt rất mạnh.
    if (($item->current_gap ?? 0) === 0) {
      $score *= 0.05;
    }

    if ($item->current_gap_db === 0) {
      $score *= 0.5;
    }

    if ($gapScore > 0.9 && $gapScore < 1.2) {
      $score *= 1.2;
    }

    if ($item->total_count_90_days == 0) {
      $score *= 0.3;
    }

    return $score;
  }

  protected function normalize($value, array $minMax): float
  {
    if ($minMax['max'] == $minMax['min']) return 0;

    return ($value - $minMax['min']) / ($minMax['max'] - $minMax['min']);
  }

  protected function getMinMax(Collection $stats): array
  {
    return [
      '7' => [
        'min' => $stats->min('total_count_7_days'),
        'max' => $stats->max('total_count_7_days'),
      ],
      '30' => [
        'min' => $stats->min('total_count_30_days'),
        'max' => $stats->max('total_count_30_days'),
      ],
      '90' => [
        'min' => $stats->min('total_count_90_days'),
        'max' => $stats->max('total_count_90_days'),
      ],
    ];
  }


  public function rankDb(Collection $stats): Collection
  {
    if ($stats->isEmpty()) return collect();

    $min = (float) $stats->min('total_count_db');
    $max = (float) $stats->max('total_count_db');

    $minMax = [
      'db_count' => [
        'min' => log(1 + max($min, 0)),
        'max' => log(1 + max($max, 0)),
      ],
    ];

    return $stats->map(function ($item) use ($minMax) {
      $score = $this->calculateDbRankingScore($item, $minMax);

      return [
        ...$item->toArray(),
        'db_score' => round($score, 4),
      ];
    })
      ->sortByDesc('db_score')
      ->values();
  }

  protected function calculateDbRankingScore($item, array $minMax): float
  {
    $gapRatio = $item->max_gap_db > 0
      ? $item->current_gap_db / $item->max_gap_db
      : 0;

    $gapScore = min($gapRatio, 1.3);

    $mrScore = $this->calculateMeanReversionDbScore($item);

    $cooldown = $item->current_gap_db > 0
      ? min($item->current_gap_db / 15, 1)
      : 0;

    $freqLog = log(1 + max((float) ($item->total_count_db ?? 0), 0));
    $freqNorm = ($minMax['db_count']['max'] ?? 0) > ($minMax['db_count']['min'] ?? 0)
      ? ($freqLog - $minMax['db_count']['min']) / ($minMax['db_count']['max'] - $minMax['db_count']['min'])
      : 0;

    $score =
      0.4 * $gapScore +
      0.25 * $mrScore +
      0.15 * $cooldown +
      0.2 * min(max($freqNorm, 0), 1);

    return $this->applyDbRules($item, $score, $gapRatio);
  }

  protected function calculateMeanReversionDbScore($item): float
  {
    return 0;
  }

  protected function applyDbRules($item, float $score, float $gapRatio): float
  {
    // ❗ vừa nổ DB → loại mạnh
    if ($item->current_gap_db === 0) {
      return $score * 0.3;
    }

    // 🔥 sweet spot
    if ($item->std_gap_db > 0) {
      $z = ($item->current_gap_db - $item->avg_gap_db) / $item->std_gap_db;

      if ($z > 1.2 && $z < 2.2) {
        $score *= 1.25;
      }

      if ($z > 3) {
        $score *= 0.7;
      }
    }

    // ❗ chưa từng ra DB
    if ($item->max_gap_db == 0) {
      $score *= 0.2;
    }

    return $score;
  }
}
