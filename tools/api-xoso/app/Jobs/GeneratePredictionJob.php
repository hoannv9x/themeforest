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
use Illuminate\Support\Facades\DB;

class GeneratePredictionJob implements ShouldQueue
{
    use Queueable;

    private ?string $predictionDate = null;

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
        $now = now();
        $hour = (int) $now->format('H');

        // If current time is between 17:00 and 19:00 (inclusive), do not generate
        if ($hour >= 17 && $hour < 19) {
            return;
        }

        // Determine the prediction date based on current hour
        if ($hour < 17) {
            $date = $now->toDateString();
        } else {
            // $hour >= 19
            $date = $now->copy()->addDay()->toDateString();
        }
        $this->predictionDate = $date;

        $result = Result::query()->latest('id')->first();
        if (!$result || !$result->date) {
            return;
        }
        $fullStats = NumberStat::where('region', ModelsNumber::REGION_MB)->get();
        $yearStats = $this->buildYearScopedStats(ModelsNumber::REGION_MB, $date);

        $freeTopPool = $this->pickDiversifiedNumbers(
            $service->rank($yearStats),
            'ranking',
            6,
            1.0,
            $date
        );

        $freeTop = $freeTopPool
            ->sort(function ($a, $b) {
                $scoreCmp = ((float) ($b['score'] ?? 0)) <=> ((float) ($a['score'] ?? 0));
                if ($scoreCmp !== 0) {
                    return $scoreCmp;
                }
                return strcmp((string) ($a['number'] ?? ''), (string) ($b['number'] ?? ''));
            })
            ->take(2)
            ->values();

        $vipTopCandidates = $this->pickDiversifiedNumbers(
            $vipService->rank($fullStats),
            'vip_ranking',
            3,
            1.25,
            $date
        );
        $vipTop = collect();
        $vipTargetCount = 5;
        foreach ($vipTopCandidates as $candidate) {
            if ($vipTop->count() >= $vipTargetCount) {
                break;
            }
            if ($vipTop->pluck('number')->contains($candidate['number'])) {
                continue;
            }
            $vipTop->push($candidate);
        }
        $vipTop = $vipTop->take($vipTargetCount)->values();

        $freeDbTop = $this->pickDiversifiedNumbers(
            $service->rankDb($yearStats)->map(fn($item) => [
                ...$item,
                'score' => $item['db_score'] ?? 0,
            ]),
            'db_ranking',
            2,
            1.0,
            $date
        );
        $vipDbTop = $this->pickDiversifiedNumbers(
            $vipService->rankDb($fullStats)->map(fn($item) => [
                ...$item,
                'score' => $item['db_score'] ?? 0,
            ]),
            'vip_db_ranking',
            3,
            1.25,
            $date
        );

        $explains = $vipTop->map(fn($item) => $explainService->explainLoto((object) $item));
        $explainsDb = $vipDbTop->map(fn($item) => $explainService->explainDb((object) $item));

        $vipThreeDigits = $this->buildThreeDigitsPrediction(3, $vipDbTop);
        $vipBachThu = $vipTop->first()
            ? collect([$explainService->explainLoto((object) $vipTop->first())])
            : collect();

        $algorithms = [
            'ranking' => $freeTop->values(),
            'vip_ranking' => $explains,
            'vip_db_ranking' => $explainsDb,
            'db_ranking' => $freeDbTop->values(),
            'vip_3_cang' => $vipThreeDigits,
            'vip_bach_thu' => $vipBachThu->values(),
        ];

        foreach ($algorithms as $code => $data) {
            Prediction::updateOrCreate(
                [
                    'date' => $date,
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

    private function buildYearScopedStats(string $region, string $asOfDate): Collection
    {
        $asOf = Carbon::parse($asOfDate)->startOfDay();
        $yearStart = $asOf->copy()->startOfYear();

        $rows = DB::table('numbers')
            ->join('results', 'numbers.result_id', '=', 'results.id')
            ->where('results.region', $region)
            ->whereDate('results.date', '>=', $yearStart->toDateString())
            ->whereDate('results.date', '<=', $asOf->toDateString())
            ->select(['numbers.number as number', 'numbers.prize as prize', 'results.date as date'])
            ->get();

        $allDates = array_fill(0, 100, []);
        $dbDates = array_fill(0, 100, []);

        foreach ($rows as $r) {
            $num = str_pad((string) $r->number, 2, '0', STR_PAD_LEFT);
            if (!preg_match('/^\d{2}$/', $num)) {
                continue;
            }
            $idx = (int) $num;
            $d = Carbon::parse($r->date)->toDateString();
            $allDates[$idx][] = $d;
            if ($r->prize === 'db') {
                $dbDates[$idx][] = $d;
            }
        }

        $cut7 = $asOf->copy()->subDays(7)->toDateString();
        $cut30 = $asOf->copy()->subDays(30)->toDateString();
        $cut90 = $asOf->copy()->subDays(90)->toDateString();
        $cut180 = $asOf->copy()->subDays(180)->toDateString();
        $cut365 = $asOf->copy()->subDays(365)->toDateString();

        $out = [];
        for ($i = 0; $i <= 99; $i++) {
            $num = str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            $dates = $allDates[$i];
            sort($dates);
            $uniqueDates = array_values(array_unique($dates));

            $last = !empty($uniqueDates) ? end($uniqueDates) : null;
            $currentGap = $last ? Carbon::parse($last)->diffInDays($asOf) : 999;
            $gaps = [];
            $prev = null;
            foreach ($uniqueDates as $d) {
                if ($prev !== null) {
                    $gaps[] = Carbon::parse($prev)->diffInDays(Carbon::parse($d));
                }
                $prev = $d;
            }
            $maxGap = !empty($gaps) ? max($gaps) : 0;
            if ($currentGap > $maxGap) {
                $maxGap = $currentGap;
            }
            $avgGap = !empty($gaps) ? (int) round(array_sum($gaps) / count($gaps)) : 0;
            $stdGap = 0;
            if (count($gaps) >= 2) {
                $mean = array_sum($gaps) / count($gaps);
                $variance = array_sum(array_map(fn($x) => ($x - $mean) ** 2, $gaps)) / count($gaps);
                $stdGap = (int) round(sqrt($variance));
            }

            $db = $dbDates[$i];
            sort($db);
            $dbUnique = array_values(array_unique($db));
            $lastDb = !empty($dbUnique) ? end($dbUnique) : null;
            $currentGapDb = $lastDb ? Carbon::parse($lastDb)->diffInDays($asOf) : 999;
            $dbGaps = [];
            $prevDb = null;
            foreach ($dbUnique as $d) {
                if ($prevDb !== null) {
                    $dbGaps[] = Carbon::parse($prevDb)->diffInDays(Carbon::parse($d));
                }
                $prevDb = $d;
            }
            $maxGapDb = !empty($dbGaps) ? max($dbGaps) : 0;
            if ($currentGapDb > $maxGapDb) {
                $maxGapDb = $currentGapDb;
            }
            $avgGapDb = !empty($dbGaps) ? (int) round(array_sum($dbGaps) / count($dbGaps)) : 0;
            $stdGapDb = 0;
            if (count($dbGaps) >= 2) {
                $mean = array_sum($dbGaps) / count($dbGaps);
                $variance = array_sum(array_map(fn($x) => ($x - $mean) ** 2, $dbGaps)) / count($dbGaps);
                $stdGapDb = (int) round(sqrt($variance));
            }

            $total = count($dates);
            $totalDb = count($db);

            $countAfter = function (array $arr, string $cut): int {
                $c = 0;
                foreach ($arr as $d) {
                    if ($d >= $cut) $c++;
                }
                return $c;
            };

            $out[] = [
                'number' => $num,
                'region' => $region,
                'total_count' => $total,
                'total_count_db' => $totalDb,
                'total_count_7_days' => $countAfter($dates, $cut7),
                'total_count_30_days' => $countAfter($dates, $cut30),
                'total_count_90_days' => $countAfter($dates, $cut90),
                'total_count_180_days' => $countAfter($dates, $cut180),
                'total_count_365_days' => $countAfter($dates, $cut365),
                'last_appeared_at' => $last,
                'last_appeared_at_db' => $lastDb,
                'current_gap' => $currentGap,
                'max_gap' => $maxGap,
                'avg_gap' => $avgGap,
                'std_gap' => $stdGap,
                'current_gap_db' => $currentGapDb,
                'max_gap_db' => $maxGapDb,
                'avg_gap_db' => $avgGapDb,
                'std_gap_db' => $stdGapDb,
                'updated_at' => now(),
            ];
        }

        return NumberStat::hydrate($out);
    }

    private function pickDiversifiedNumbers(
        Collection $ranked,
        string $algorithm,
        int $limit,
        float $vipPenaltyMultiplier = 1.0,
        ?string $predictionDate = null
    ): Collection {
        $pool = $ranked;
        if (!str_contains($algorithm, 'db')) {
            $nonRecentPool = $ranked->filter(fn($item) => (int) ($item['current_gap'] ?? 0) > 0)->values();
            if ($nonRecentPool->count() >= $limit) {
                $pool = $nonRecentPool;
            }
        }

        $asOfDate = $predictionDate ?: $this->predictionDate ?: now()->toDateString();
        [$hitNumbers, $dbHitNumbers] = $this->getYesterdayHits($asOfDate);
        $recentPenalty = $this->recentPredictionPenalty($algorithm, 14, $asOfDate);

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
            ->sort(function ($a, $b) {
                $scoreCmp = ((float) ($b['score'] ?? 0)) <=> ((float) ($a['score'] ?? 0));
                if ($scoreCmp !== 0) {
                    return $scoreCmp;
                }
                return strcmp((string) ($a['number'] ?? ''), (string) ($b['number'] ?? ''));
            })
            ->take($limit)
            ->values();
    }

    private function getYesterdayHits(string $predictionDate): array
    {
        $hitDate = Carbon::parse($predictionDate)->subDay()->toDateString();
        $result = Result::query()
            ->where('region', ModelsNumber::REGION_MB)
            ->whereDate('date', $hitDate)
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

    private function recentPredictionPenalty(string $algorithm, int $days, string $predictionDate): array
    {
        $endDate = Carbon::parse($predictionDate)->toDateString();
        $startDate = Carbon::parse($predictionDate)->subDays($days)->toDateString();
        $recentPredictions = Prediction::query()
            ->where('region', ModelsNumber::REGION_MB)
            ->where('algorithm', $algorithm)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<', $endDate)
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

        $anchor = Carbon::parse($this->predictionDate ?: now()->toDateString());
        $cutoff = $anchor->copy()->subDays(180)->toDateString();

        $dbNumbers = collect($vipDbTop)->pluck('number')->filter()->toArray();

        foreach ($dbNumbers as $dbNum) {
            $dbNumStr = str_pad((string)$dbNum, 2, '0', STR_PAD_LEFT);
            
            for ($prefix = 0; $prefix <= 9; $prefix++) {
                $threeDigits = $prefix . $dbNumStr;
                
                $rows = Number::query()
                    ->join('results', 'results.id', '=', 'numbers.result_id')
                    ->where('results.region', ModelsNumber::REGION_MB)
                    ->where('numbers.prize', 'db')
                    ->whereDate('results.date', '>=', $cutoff)
                    ->whereRaw('RIGHT(LPAD(REPLACE(numbers.raw_number, "[^0-9]", ""), 3, "0"), 3) = ?', [$threeDigits])
                    ->orderByDesc('results.date')
                    ->get(['results.date']);

                $freq = $rows->count();
                $lastDate = $rows->first()?->date;
                $gapDays = $lastDate ? Carbon::parse($lastDate)->diffInDays($anchor) : 999;

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
