<?php

namespace App\Services;

use App\Models\Number;
use App\Models\NumberStat;
use App\Models\Prediction;

class PredictionService
{
  public function getGan($region)
  {
    return NumberStat::orderByDesc('current_gap')
      ->take(10)
      ->get()
      ->map(fn($n) => [
        'number' => $n->number,
        'score' => $n->current_gap
      ]);
  }

  public function getTrend($region)
  {
    return Number::selectRaw('numbers.number, COUNT(numbers.id) as total')
      ->join('results', 'results.id', '=', 'numbers.result_id')
      ->where('results.date', '>=', now()->subDays(7))
      ->where('results.region', $region)
      ->groupBy('numbers.number')
      ->orderByDesc('total')
      ->limit(10)
      ->get()
      ->map(fn($n) => [
        'number' => $n->numbers_number,
        'score' => $n->total
      ]);
  }

  public function getRoi()
  {
    $yesterday = Number::join('results', 'results.id', '=', 'numbers.result_id')
      ->where('results.date', '>=', now()->subDay())
      ->pluck('numbers.number');

    $today = Number::join('results', 'results.id', '=', 'numbers.result_id')
      ->where('results.date', '>=', now())
      ->pluck('numbers.number');

    return $today->intersect($yesterday)
      ->map(fn($n) => [
        'number' => $n,
        'score' => 1
      ]);
  }

  public function getAI()
  {
    $stats = NumberStat::all();

    $maxGap = $stats->max('current_gap');
    $maxCount = $stats->max('total_count');

    return $stats->map(function ($n) use ($maxGap, $maxCount) {

      $gapScore = $maxGap ? $n->current_gap / $maxGap : 0;
      $countScore = $maxCount ? $n->total_count / $maxCount : 0;

      $score = ($gapScore * 0.7) + ($countScore * 0.3);

      return [
        'number' => $n->number,
        'score' => round($score * 100)
      ];
    })->sortByDesc('score')->take(10)->values();
  }

  public function generate(): array
  {
    $stats = NumberStat::all();
    $minGap = $stats->min('current_gap');
    $maxGap = $stats->max('current_gap');

    $minCount = $stats->min('total_count');
    $maxCount = $stats->max('total_count');


    $data = $stats->map(function ($n) use ($minGap, $maxGap, $minCount, $maxCount) {
      $gapScore   = $this->normalize($n->current_gap, $minGap, $maxGap);
      $countScore = $this->normalize($n->total_count, $minCount, $maxCount);

      $score = round(($gapScore * 60) + ($countScore * 40), 0);

      return [
        'number' => $n->number,
        'score' => $score < 90 ? $score : 90,
        'type' => $this->detectType($n),
        'reason' => $this->buildReason($n),
      ];
    });

    $totalScore = $data->sum('score');
    $top = $data->sortByDesc('score')->take(5)->values();

    $topWithConfidence = $top->map(function ($item) use ($totalScore) {
      return $item;
    });

    return [
      'numbers' => $topWithConfidence,
      'meta' => [
        'algorithm' => 'weighted_v2',
        'confidence_avg' => round($topWithConfidence->avg('confidence')),
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

  function normalize($value, $min, $max)
  {
    if ($max == $min) return 0;
    return ($value - $min) / ($max - $min) ?? 0;
  }

  function getPrediction($region): array
  {
    $predictions = Prediction::where('region', $region)
      ->where('date', now()->toDateString())
      ->get()
      ->keyBy('algorithm')
      ->toArray();

    return $predictions;
  }

  function getPredictionPro($region): array
  {
    $predictions = $this->getPrediction($region);
    $numberCounts = [];

    foreach ($predictions as $algo => $value) {
      foreach ($value['numbers'] as $data) {
        $num = $data['number'] ?? null;
        if ($num !== null) {
          $numberCounts[$num] = ($numberCounts[$num] ?? 0) + 1;
        }
      }
    }
    arsort($numberCounts);
    $topNumbers = array_slice($numberCounts, 0, 5, true);

    return [
      'predictions' => $predictions,
      'top_numbers' => $topNumbers
    ];
  }
}
