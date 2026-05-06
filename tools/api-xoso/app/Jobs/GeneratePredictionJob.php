<?php

namespace App\Jobs;

use App\Models\Number as ModelsNumber;
use App\Models\NumberStat;
use App\Models\Number;
use App\Models\Prediction;
use App\Models\Result;
use App\Services\ExplainService;
use App\Services\ScoreService;
use App\Services\ScoreServiceVip;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class GeneratePredictionJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ScoreService $service, ScoreServiceVip $vipService, ExplainService $explainService)
    {
        $stats = NumberStat::where('region', ModelsNumber::REGION_MB)->get();
        $sharedTop = $this->pickDiversifiedNumbers(
            $service->rank($stats),
            'ranking',
            6
        );
        $sharedTopByNumber = $sharedTop->keyBy('number');

        $freeTop = $sharedTop->shuffle()->take(3)->values();
        $vipFromShared = $sharedTop
            ->reject(fn($item) => $freeTop->pluck('number')->contains($item['number']))
            ->values();

        $vipTopCandidates = $this->pickDiversifiedNumbers(
            $vipService->rank($stats),
            'vip_ranking',
            10,
            1.25
        );
        $vipTop = collect($vipFromShared);
        $vipTargetCount = 8;
        foreach ($vipTopCandidates as $candidate) {
            if ($vipTop->count() >= $vipTargetCount) {
                break;
            }
            if ($sharedTopByNumber->has($candidate['number']) || $vipTop->pluck('number')->contains($candidate['number'])) {
                continue;
            }
            $vipTop->push($candidate);
        }
        $vipTop = $vipTop->take($vipTargetCount)->values();

        $freeDbTop = $this->pickDiversifiedNumbers(
            $service->rankDb($stats)->map(fn($item) => [
                ...$item,
                'score' => $item['db_score'] ?? 0,
            ]),
            'db_ranking',
            2
        );
        $vipDbTop = $this->pickDiversifiedNumbers(
            $vipService->rankDb($stats)->map(fn($item) => [
                ...$item,
                'score' => $item['db_score'] ?? 0,
            ]),
            'vip_db_ranking',
            5,
            1.25
        );

        $explains = $vipTop->map(fn($item) => $explainService->explainLoto((object) $item));
        $explainsDb = $vipDbTop->map(fn($item) => $explainService->explainDb((object) $item));

        $vipThreeDigits = $this->buildThreeDigitsPrediction(3, $vipDbTop);

        $algorithms = [
            'ranking' => $freeTop->values(),
            'vip_ranking' => $explains,
            'vip_db_ranking' => $explainsDb,
            'db_ranking' => $freeDbTop->values(),
            'vip_3_cang' => $vipThreeDigits,
        ];

        foreach ($algorithms as $code => $data) {
            Prediction::updateOrCreate(
                [
                    'date' => now()->toDateString(),
                    'region' => ModelsNumber::REGION_MB,
                    'algorithm' => $code,
                ],
                [
                    'numbers' => $data,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function pickDiversifiedNumbers(
        Collection $ranked,
        string $algorithm,
        int $limit,
        float $vipPenaltyMultiplier = 1.0
    ): Collection {
        $pool = $ranked;
        if (!str_contains($algorithm, 'db')) {
            $nonRecentPool = $ranked->filter(fn($item) => (int) ($item['current_gap'] ?? 0) > 0)->values();
            if ($nonRecentPool->count() >= $limit) {
                $pool = $nonRecentPool;
            }
        }

        [$hitNumbers, $dbHitNumbers] = $this->getYesterdayHits();
        $recentPenalty = $this->recentPredictionPenalty($algorithm, 14);

        return $pool
            ->map(function ($item) use ($recentPenalty, $hitNumbers, $dbHitNumbers, $algorithm, $vipPenaltyMultiplier) {
                $number = $this->normalizeNumber($item['number'] ?? null);
                $baseScore = (float) ($item['score'] ?? 0);
                $penalty = ($recentPenalty[$number] ?? 0) * 0.08 * $vipPenaltyMultiplier;

                $isDbAlgo = str_contains($algorithm, 'db');

                // Confidence is a probability-like score (0..100) derived from gap + cycle stats.
                // It is intentionally *independent* from the diversification penalties so it can be explained to users.
                if ($isDbAlgo) {
                    $gapRatio = ((float) ($item['max_gap_db'] ?? 0)) > 0
                        ? ((float) ($item['current_gap_db'] ?? 0)) / ((float) ($item['max_gap_db'] ?? 0))
                        : 0.0;
                    $z = 0.0;
                    $std = (float) ($item['std_gap_db'] ?? 0);
                    $avg = (float) ($item['avg_gap_db'] ?? 0);
                    $cur = (float) ($item['current_gap_db'] ?? 0);
                    if ($std > 0 && $avg > 0) {
                        $z = ($cur - $avg) / $std;
                    }
                    $sigmoid = 1 / (1 + exp(-$z));
                    $confidence = (0.75 * $sigmoid) + (0.25 * min($gapRatio, 1.0));
                } else {
                    $gapRatio = ((float) ($item['max_gap'] ?? 0)) > 0
                        ? ((float) ($item['current_gap'] ?? 0)) / ((float) ($item['max_gap'] ?? 0))
                        : 0.0;
                    $z = 0.0;
                    $std = (float) ($item['std_gap'] ?? 0);
                    $avg = (float) ($item['avg_gap'] ?? 0);
                    $cur = (float) ($item['current_gap'] ?? 0);
                    if ($std > 0 && $avg > 0) {
                        $z = ($cur - $avg) / $std;
                    }
                    $sigmoid = 1 / (1 + exp(-$z));

                    $short = (float) ($item['total_count_7_days'] ?? 0);
                    $mid = max(((float) ($item['total_count_30_days'] ?? 0)) / 4.0, 1.0);
                    $trend = $mid > 0 ? ($short / $mid) : 0.0;

                    $confidence = (0.65 * $sigmoid)
                        + (0.25 * (min($trend, 2.0) / 2.0))
                        + (0.10 * (1.0 - min($gapRatio, 1.0)));
                }
                $item['confidence'] = round(min(max($confidence, 0.0), 1.0) * 100, 2);

                $hitSet = $isDbAlgo ? $dbHitNumbers : $hitNumbers;
                $missYesterdayPenalty = $hitSet->contains($number) ? 0 : 0.15 * $vipPenaltyMultiplier;

                $item['number'] = $number;
                $item['score'] = round($baseScore - $penalty - $missYesterdayPenalty, 6);
                return $item;
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    private function getYesterdayHits(): array
    {
        $result = Result::query()
            ->where('region', ModelsNumber::REGION_MB)
            ->whereDate('date', Carbon::yesterday()->toDateString())
            ->latest('id')
            ->first();

        if (!$result) {
            return [collect(), collect()];
        }

        $allHits = Number::query()
            ->where('result_id', $result->id)
            ->pluck('number')
            ->map(fn($number) => $this->normalizeNumber($number))
            ->unique()
            ->values();

        $dbHits = Number::query()
            ->where('result_id', $result->id)
            ->where('prize', 'db')
            ->pluck('number')
            ->map(fn($number) => $this->normalizeNumber($number))
            ->unique()
            ->values();

        return [$allHits, $dbHits];
    }

    private function recentPredictionPenalty(string $algorithm, int $days): array
    {
        $recentPredictions = Prediction::query()
            ->where('region', ModelsNumber::REGION_MB)
            ->where('algorithm', $algorithm)
            ->whereDate('date', '>=', now()->subDays($days)->toDateString())
            ->orderBy('date', 'desc')
            ->get(['numbers', 'date']);

        $penalty = [];
        foreach ($recentPredictions as $index => $prediction) {
            $decay = max(0.2, 1 - ($index * 0.08));
            foreach (($prediction->numbers ?? []) as $item) {
                $number = $this->normalizeNumber($item['number'] ?? null);
                if ($number === null) {
                    continue;
                }
                $penalty[$number] = ($penalty[$number] ?? 0) + $decay;
            }
        }

        return $penalty;
    }

    private function buildXien(Collection $baseNumbers, int $size, int $limit): array
    {
        $top = $baseNumbers->pluck('number')->filter()->take(10)->values()->all();
        if (count($top) < $size) {
            return [];
        }

        $scored = [];
        foreach ($this->combinations($top, $size) as $combo) {
            $comboScore = collect($combo)
                ->map(function ($num) use ($baseNumbers) {
                    return (float) ($baseNumbers->firstWhere('number', $num)['score'] ?? 0);
                })
                ->sum();

            $comboConfidence = collect($combo)
                ->map(function ($num) use ($baseNumbers) {
                    return (float) ($baseNumbers->firstWhere('number', $num)['confidence'] ?? 0);
                })
                ->avg();

            $scored[] = [
                'numbers' => $combo,
                'score' => round($comboScore, 4),
                'confidence' => $comboConfidence === null ? null : round((float) $comboConfidence, 2),
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($scored, 0, $limit);
    }

    private function combinations(array $items, int $size, int $start = 0, array $path = []): array
    {
        if (count($path) === $size) {
            return [$path];
        }

        $result = [];
        for ($i = $start; $i < count($items); $i++) {
            $nextPath = [...$path, $items[$i]];
            $result = [...$result, ...$this->combinations($items, $size, $i + 1, $nextPath)];
        }

        return $result;
    }

    private function buildThreeDigitsPrediction(int $limit, $vipDbTop = []): array
    {
        $predictions = [];
        
        if (empty($vipDbTop)) {
            return [];
        }

        $dbNumbers = collect($vipDbTop)->pluck('number')->filter()->toArray();

        foreach ($dbNumbers as $dbNum) {
            $dbNumStr = str_pad((string)$dbNum, 2, '0', STR_PAD_LEFT);
            
            for ($prefix = 0; $prefix <= 9; $prefix++) {
                $threeDigits = $prefix . $dbNumStr;
                
                $rows = Number::query()
                    ->join('results', 'results.id', '=', 'numbers.result_id')
                    ->where('results.region', ModelsNumber::REGION_MB)
                    ->where('numbers.prize', 'db')
                    ->whereDate('results.date', '>=', now()->subDays(180)->toDateString())
                    ->whereRaw('RIGHT(LPAD(REPLACE(numbers.raw_number, "[^0-9]", ""), 3, "0"), 3) = ?', [$threeDigits])
                    ->orderByDesc('results.date')
                    ->get(['results.date']);

                $freq = $rows->count();
                $lastDate = $rows->first()?->date;
                $gapDays = $lastDate ? Carbon::parse($lastDate)->diffInDays(now()) : 999;

                $freqScore = min($freq / 5, 1);
                $gapScore = min($gapDays / 30, 1);
                $score = (0.5 * $freqScore) + (0.5 * $gapScore);

                $predictions[] = [
                    'number' => $threeDigits,
                    'score' => round($score, 4),
                    'confidence' => round($score * 100, 2),
                    'last_hit_days' => $gapDays,
                    'freq_180' => $freq,
                    'based_on_db' => $dbNumStr,
                ];
            }
        }

        usort($predictions, fn($a, $b) => $b['score'] <=> $a['score']);
        $uniquePredictions = [];
        $seen = [];
        foreach ($predictions as $p) {
            if (!in_array($p['number'], $seen)) {
                $seen[] = $p['number'];
                $uniquePredictions[] = $p;
            }
        }
        return array_slice($uniquePredictions, 0, $limit);
    }

    private function normalizeNumber($number): ?string
    {
        if ($number === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $number);
        if ($digits === '') {
            return null;
        }

        return str_pad(substr($digits, -2), 2, '0', STR_PAD_LEFT);
    }
}
