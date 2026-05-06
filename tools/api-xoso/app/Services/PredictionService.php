<?php

namespace App\Services;

use App\Models\Number;
use App\Models\NumberStat;
use App\Models\Prediction;
use App\Models\Result;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isNumeric;

class PredictionService
{
  public function getYesterdayPredictionWithHits($region, $vip = false): array
  {
    $yesterday = Carbon::yesterday()->toDateString();
    $predictionBundle = $this->getPredictionProByDate($region, $yesterday, $vip);
    $predictions = $predictionBundle['predictions'] ?? [];

    $result = Result::where('region', $region)
      ->whereDate('date', $yesterday)
      ->latest('id')
      ->first();

    $allDrawnNumbers = collect();
    $dbDrawnNumbers = collect();

    if ($result) {
      $allDrawnNumbers = Number::where('result_id', $result->id)
        ->pluck('number')
        ->map(fn($number) => str_pad((string) $number, 2, '0', STR_PAD_LEFT))
        ->unique()
        ->values();

      $dbDrawnNumbers = Number::where('result_id', $result->id)
        ->where('prize', 'db')
        ->pluck('number')
        ->map(fn($number) => str_pad((string) $number, 2, '0', STR_PAD_LEFT))
        ->unique()
        ->values();
    }

    $lotoNumbers = collect($predictions['ranking']['numbers'] ?? []);
    $dbNumbers = collect($predictions['db_ranking']['numbers'] ?? []);

    $lotoWithHit = $lotoNumbers->map(function ($item) use ($allDrawnNumbers) {
      $predicted = str_pad((string) ($item['number'] ?? ''), 2, '0', STR_PAD_LEFT);
      $item['is_hit'] = $predicted !== '' && $allDrawnNumbers->contains($predicted);
      return $item;
    })->values();

    $dbWithHit = $dbNumbers->map(function ($item) use ($dbDrawnNumbers) {
      $predicted = str_pad((string) ($item['number'] ?? ''), 2, '0', STR_PAD_LEFT);
      $item['is_hit'] = $predicted !== '' && $dbDrawnNumbers->contains($predicted);
      return $item;
    })->values();

    $lotoHits = $lotoWithHit->where('is_hit', true)->pluck('number')->values();
    $dbHits = $dbWithHit->where('is_hit', true)->pluck('number')->values();

    return [
      'date' => Carbon::parse($yesterday)->format('d-m-Y'),
      'has_result' => (bool) $result,
      'predictions' => [
        'ranking' => [
          ...($predictions['ranking'] ?? []),
          'numbers' => $lotoWithHit,
        ],
        'db_ranking' => [
          ...($predictions['db_ranking'] ?? []),
          'numbers' => $dbWithHit,
        ],
      ],
      'hits' => [
        'loto' => $lotoHits,
        'db' => $dbHits,
      ],
      'stats' => [
        'loto_total' => $lotoWithHit->count(),
        'loto_hit_count' => $lotoHits->count(),
        'db_total' => $dbWithHit->count(),
        'db_hit_count' => $dbHits->count(),
        'total_hit_count' => $lotoHits->count() + $dbHits->count(),
      ],
    ];
  }

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

  public function getVip($region = Number::REGION_MB)
  {
    $stats = NumberStat::where('region', $region)->get();

    $ranking = app(ScoreService::class)->rank($stats);

    return $ranking->take(10);
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

  public function getRoi($region)
  {
    $results = Result::where('region', $region)
      ->orderBy('date', 'desc')
      ->take(30)
      ->get();

    $roiStats = [];

    for ($i = 0; $i < count($results) - 1; $i++) {

      $today = Number::where('result_id', $results[$i]->id)->pluck('number');
      $yesterday = Number::where('result_id', $results[$i + 1]->id)->pluck('number');

      $roi = $today->intersect($yesterday);

      foreach ($roi as $n) {
        if (!is_numeric($n)) {
          continue;
        }
        $roiStats[$n] = ($roiStats[$n] ?? 0) + 1;
      }
    }

    $latestResult = $results->first();

    $candidates = Number::where('result_id', $latestResult->id)
      ->pluck('number')
      ->unique();

    // 🎯 Combine candidate + score
    return $candidates->map(function ($n) use ($roiStats) {
      return [
        'number' => $n,
        'score' => ($roiStats[$n] ?? 0) * 10
      ];
    })
      ->sortByDesc('score')
      ->take(10)
      ->values();
  }

  public function getAI($region)
  {
    $stats = NumberStat::where('region', $region)->get();

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

  function getPredictionPro($region, $vip = false): array
  {
    return $this->getPredictionProByDate($region, now()->toDateString(), $vip);
  }

  function getPredictionProByDate($region, $date, $vip = false): array
  {
    $predictions = Prediction::where('region', $region)
      ->whereDate('date', $date)
      ->get()
      ->keyBy('algorithm')
      ->toArray();
    $numberCounts = [];
    $heatmapData = [];
    $cycleStats = [];
    $strategySuggestions = [];

    $heatmapAlgos = $vip
      ? ['vip_ranking', 'vip_db_ranking']
      : ['ranking', 'db_ranking'];

    foreach ($predictions as $algo => $value) {
      if (!in_array($algo, $heatmapAlgos, true)) {
        continue;
      }
      $numbers = $value['numbers'] ?? [];
      foreach ($numbers as $data) {
        $num = $data['number'] ?? null;
        if ($num !== null) {
          $confidence = isset($data['confidence']) ? (float) $data['confidence'] : 1.0;
          $numberCounts[$num] = ($numberCounts[$num] ?? 0) + $confidence;
          
          if ($vip) {
            $heatmapData[$num] = [
              'number' => $num,
              'confidence' => $confidence,
              'score' => $data['score'] ?? 0,
              'signals' => $data['signals'] ?? null,
              'last_hit_days' => $data['last_hit_days'] ?? null,
              'label' => $data['label'] ?? null,
              'message' => $data['message'] ?? null,
            ];
          }
        }
      }
    }
    arsort($numberCounts);
    $topNumbers = array_slice($numberCounts, 0, 5, true);

    if (Auth::check() && Auth::user()->role == User::ROLE_VIP && $vip) {
      $predictions = [
        'db_ranking'   => $predictions['vip_db_ranking'] ?? null,
        'ranking' => $predictions['vip_ranking'] ?? null,
        'three_cang' => $predictions['vip_3_cang'] ?? null,
      ];
      
      $stats = NumberStat::where('region', $region)->get();
      $statsByNumber = $stats->keyBy('number');
      
      $cycleStats = $stats->map(function ($stat) {
        return [
          'number' => $stat->number,
          'current_gap' => $stat->current_gap,
          'max_gap' => $stat->max_gap,
          'avg_gap' => $stat->avg_gap,
          'std_gap' => $stat->std_gap,
          'total_count' => $stat->total_count,
          'total_count_7_days' => $stat->total_count_7_days,
          'total_count_30_days' => $stat->total_count_30_days,
          'current_gap_db' => $stat->current_gap_db,
          'max_gap_db' => $stat->max_gap_db,
          'avg_gap_db' => $stat->avg_gap_db,
          'std_gap_db' => $stat->std_gap_db,
        ];
      })->values()->toArray();
      
      for ($i = 0; $i <= 99; $i++) {
        $num = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
        $stat = $statsByNumber->get($num);
        $predictionData = $heatmapData[$num] ?? null;
        
        $lastHitDate = null;
        $lastDbHitDate = null;
        
        if ($stat) {
          $recentLotoResult = Result::where('region', $region)
            ->whereHas('numbers', function ($q) use ($num) {
              $q->where('number', $num)->whereNotIn('prize', ['db', 'ma_db']);
            })
            ->latest('date')
            ->first();
          $lastHitDate = $recentLotoResult?->date;
          
          $recentDbResult = Result::where('region', $region)
            ->whereHas('numbers', function ($q) use ($num) {
              $q->where('number', $num)->where('prize', 'db');
            })
            ->latest('date')
            ->first();
          $lastDbHitDate = $recentDbResult?->date;
        }
        
        $heatmapData[$num] = [
          'number' => $num,
          'confidence' => $predictionData['confidence'] ?? 0,
          'score' => $predictionData['score'] ?? 0,
          'signals' => $predictionData['signals'] ?? null,
          'label' => $predictionData['label'] ?? null,
          'message' => $predictionData['message'] ?? null,
          'total_count' => $stat?->total_count ?? 0,
          'total_count_db' => $stat?->total_count_db ?? 0,
          'last_hit_date' => $lastHitDate,
          'last_db_hit_date' => $lastDbHitDate,
          'current_gap' => $stat?->current_gap ?? 0,
          'max_gap' => $stat?->max_gap ?? 0,
          'current_gap_db' => $stat?->current_gap_db ?? 0,
          'max_gap_db' => $stat?->max_gap_db ?? 0,
          'avg_gap' => $stat?->avg_gap ?? 0,
          'std_gap' => $stat?->std_gap ?? 0,
          'is_in_prediction' => isset($heatmapData[$num]) && isset($predictionData),
        ];
      }
      
      ksort($heatmapData);
      
      $strategySuggestions = $this->generateStrategySuggestions($topNumbers, $cycleStats);
    } else {
      $predictions = [
        'db_ranking'   => $predictions['db_ranking'] ?? null,
        'ranking' => $predictions['ranking'] ?? null,
      ];
    }

    $result = [
      'predictions' => $predictions,
      'top_numbers' => $topNumbers
    ];
    
    if ($vip) {
      $result['heatmap'] = $heatmapData;
      $result['cycle_stats'] = $cycleStats;
      $result['strategy_suggestions'] = $strategySuggestions;
      $result['is_vip'] = true;
    }

    return $result;
  }
  
  private function generateStrategySuggestions($topNumbers, $cycleStats): array
  {
    $suggestions = [];
    
    $avgConfidence = collect($topNumbers)->avg() ?: 0;
    
    if ($avgConfidence > 70) {
      $suggestions[] = [
        'type' => 'strong',
        'title' => 'Đánh mạnh hôm nay',
        'description' => 'Tỷ lệ xác suất cao, bạn có thể đánh mạnh hơn.',
        'priority' => 1,
      ];
    } elseif ($avgConfidence > 50) {
      $suggestions[] = [
        'type' => 'normal',
        'title' => 'Đánh bình thường',
        'description' => 'Tỷ lệ xác suất trung bình, hãy đánh theo ngân sách.',
        'priority' => 2,
      ];
    } else {
      $suggestions[] = [
        'type' => 'weak',
        'title' => 'Nên nghỉ hoặc đánh nhẹ',
        'description' => 'Tỷ lệ xác suất thấp hôm nay, hãy cân nhắc.',
        'priority' => 3,
      ];
    }
    
    return $suggestions;
  }
}
