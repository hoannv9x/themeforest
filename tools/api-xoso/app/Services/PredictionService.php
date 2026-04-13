<?php

namespace App\Services;

use App\Models\NumberStat;

class PredictionService
{
  public function generate(): array
  {
    $stats = NumberStat::all();

    $data = $stats->map(function ($n) {
      $score = ($n->current_gap * 2) + $n->total_count;

      return [
        'number' => $n->number,
        'score' => $score,
        'type' => $this->detectType($n),
        'reason' => $this->buildReason($n),
      ];
    });

    $top = $data->sortByDesc('score')->take(5)->values();

    return [
      'numbers' => $top,
      'meta' => [
        'algorithm' => 'weighted_v2',
        'confidence_avg' => round($top->avg('score')),
        'generated_at' => now()->toDateTimeString()
      ]
    ];
  }

  private function detectType($n): string
  {
    if ($n->current_gap > 7) return 'hot';
    if ($n->current_gap < 2) return 'cold';
    return 'normal';
  }

  private function buildReason($n): string
  {
    if ($n->current_gap > 7) return 'Lô gan lâu chưa ra';
    if ($n->total_count > 50) return 'Tần suất cao';
    return 'Chu kỳ trung bình';
  }
}
