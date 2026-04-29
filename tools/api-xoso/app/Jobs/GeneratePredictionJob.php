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
        foreach ($vipTopCandidates as $candidate) {
            if ($vipTop->count() >= 5) {
                break;
            }
            if ($sharedTopByNumber->has($candidate['number']) || $vipTop->pluck('number')->contains($candidate['number'])) {
                continue;
            }
            $vipTop->push($candidate);
        }
        $vipTop = $vipTop->take(5)->values();

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

        $vipXien2 = $this->buildXien($vipTop, 2, 5);
        $vipXien3 = $this->buildXien($vipTop, 3, 5);
        $vipXien4 = $this->buildXien($vipTop, 4, 3);
        $vipThreeDigits = $this->buildThreeDigitsPrediction(3);

        $algorithms = [
            'ranking' => $freeTop->values(),
            'vip_ranking' => $explains,
            'vip_db_ranking' => $explainsDb,
            'db_ranking' => $freeDbTop->values(),
            'vip_xien_2' => $vipXien2,
            'vip_xien_3' => $vipXien3,
            'vip_xien_4' => $vipXien4,
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

            $scored[] = [
                'numbers' => $combo,
                'score' => round($comboScore, 4),
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

    private function buildThreeDigitsPrediction(int $limit): array
    {
        $rows = Number::query()
            ->join('results', 'results.id', '=', 'numbers.result_id')
            ->where('results.region', ModelsNumber::REGION_MB)
            ->where('numbers.prize', 'db')
            ->whereDate('results.date', '>=', now()->subDays(180)->toDateString())
            ->orderByDesc('results.date')
            ->get(['numbers.raw_number', 'results.date']);

        if ($rows->isEmpty()) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $raw = preg_replace('/\D/', '', (string) $row->raw_number);
            $three = substr(str_pad($raw, 3, '0', STR_PAD_LEFT), -3);
            if ($three === false || $three === '') {
                continue;
            }
            if (!isset($grouped[$three])) {
                $grouped[$three] = ['freq' => 0, 'last_date' => null];
            }
            $grouped[$three]['freq']++;
            $grouped[$three]['last_date'] = $grouped[$three]['last_date'] ?? $row->date;
        }

        $predictions = [];
        $maxFreq = max(array_column($grouped, 'freq'));
        foreach ($grouped as $three => $data) {
            $gapDays = Carbon::parse($data['last_date'])->diffInDays(now());
            $freqRatio = $maxFreq > 0 ? $data['freq'] / $maxFreq : 0;
            $gapScore = min($gapDays / 20, 1);
            $score = (0.6 * $gapScore) + (0.4 * (1 - $freqRatio));

            $predictions[] = [
                'number' => $three,
                'score' => round($score, 4),
                'last_hit_days' => $gapDays,
                'freq_180' => $data['freq'],
            ];
        }

        usort($predictions, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($predictions, 0, $limit);
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
