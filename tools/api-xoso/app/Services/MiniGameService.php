<?php

namespace App\Services;

use App\Models\MiniGameDailyStat;
use App\Models\MiniGamePayoutRequest;
use App\Models\MiniGamePrediction;
use App\Models\MiniGameWeeklyScore;
use App\Models\Number;
use App\Models\Result;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MiniGameService
{
    public function getOverview(?User $user = null, ?Carbon $date = null): array
    {
        $date = $date ?: now();
        $predictionDate = $date->toDateString();
        $stats = $this->buildStatsFromPredictions($predictionDate);
        $myPrediction = $user ? $this->getMyPrediction($user, $date) : null;

        return [
            'date' => $predictionDate,
            'cutoff_at' => $this->cutoffAt($date)->toIso8601String(),
            'countdown_seconds' => max(0, now()->diffInSeconds($this->cutoffAt($date), false)),
            'total_users' => $stats['total_users'],
            'leader' => $stats['leader'],
            'top_numbers' => $stats['top_numbers'],
            'predicted_numbers' => $stats['predicted_numbers'],
            'ai_suggestion' => $this->buildAiSuggestion($stats['top_numbers']),
            'my_prediction' => $myPrediction,
        ];
    }

    public function submitPrediction(User $user, array $numbers, ?Carbon $date = null): MiniGamePrediction
    {
        $date = $date ?: now();
        if (now()->greaterThan($this->cutoffAt($date))) {
            throw new \InvalidArgumentException('Da qua 17:00, khong the cap nhat du doan hom nay.');
        }

        $normalized = $this->normalizeNumbers($numbers);
        if (count($normalized) === 0) {
            throw new \InvalidArgumentException('Vui long chon it nhat 1 so.');
        }
        if (count($normalized) > 5) {
            throw new \InvalidArgumentException('Moi user chi du doan toi da 5 so.');
        }

        $prediction = MiniGamePrediction::updateOrCreate(
            [
                'user_id' => $user->id,
                'prediction_date' => $date->toDateString(),
            ],
            [
                'numbers' => array_values($normalized),
                'source' => 'user',
                'predictor_name' => null,
            ]
        );

        MiniGameDailyStat::where('prediction_date', $date->toDateString())->delete();
        return $prediction;
    }

    public function getMyPrediction(User $user, ?Carbon $date = null): ?array
    {
        $date = $date ?: now();
        $prediction = MiniGamePrediction::query()
            ->where('user_id', $user->id)
            ->whereDate('prediction_date', $date->toDateString())
            ->first();

        if (!$prediction) {
            return null;
        }

        return [
            'id' => $prediction->id,
            'numbers' => $prediction->numbers,
            'created_at' => optional($prediction->created_at)?->toDateTimeString(),
            'updated_at' => optional($prediction->updated_at)?->toDateTimeString(),
        ];
    }

    public function finalizeDaily(Carbon $date): array
    {
        $predictionDate = $date->toDateString();
        $stats = $this->buildStatsFromPredictions($predictionDate);
        $aiSuggestion = $this->buildAiSuggestion($stats['top_numbers']);

        $record = MiniGameDailyStat::updateOrCreate(
            ['prediction_date' => $predictionDate],
            [
                'total_participants' => $stats['total_users'],
                'top_numbers' => $stats['top_numbers'],
                'predicted_numbers' => $stats['predicted_numbers'],
                'leader_number' => $stats['leader']['number'] ?? null,
                'leader_votes' => $stats['leader']['votes'] ?? 0,
                'ai_suggestion' => $aiSuggestion,
                'cutoff_at' => $this->cutoffAt($date),
                'finalized_at' => now(),
            ]
        );

        $hits = $this->drawnNumbers($predictionDate);
        $predictions = MiniGamePrediction::query()->whereDate('prediction_date', $predictionDate)->get();
        foreach ($predictions as $prediction) {
            $hitCount = collect($prediction->numbers)
                ->filter(fn($num) => in_array($num, $hits, true))
                ->count();

            $prediction->update([
                'hit_count' => $hitCount,
                'evaluated_at' => now(),
            ]);
        }

        return $record->toArray();
    }

    public function computeWeeklyScores(Carbon $weekStart, Carbon $weekEnd): Collection
    {
        $rows = MiniGamePrediction::query()
            ->whereNotNull('user_id')
            ->whereBetween('prediction_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->groupBy('user_id')
            ->selectRaw('user_id, SUM(hit_count) as correct_count')
            ->get();

        return DB::transaction(function () use ($rows, $weekStart, $weekEnd) {
            $scores = collect();
            foreach ($rows as $row) {
                $scores->push(MiniGameWeeklyScore::updateOrCreate(
                    [
                        'week_start' => $weekStart->toDateString(),
                        'week_end' => $weekEnd->toDateString(),
                        'user_id' => $row->user_id,
                    ],
                    [
                        'correct_count' => (int) $row->correct_count,
                        'is_winner' => false,
                    ]
                ));
            }
            return $scores;
        });
    }

    public function pickWeeklyWinner(Carbon $weekStart, Carbon $weekEnd): ?MiniGameWeeklyScore
    {
        $scores = MiniGameWeeklyScore::query()
            ->whereDate('week_start', $weekStart->toDateString())
            ->whereDate('week_end', $weekEnd->toDateString())
            ->where('correct_count', '>', 10)
            ->orderByDesc('correct_count')
            ->get();

        if ($scores->isEmpty()) {
            return null;
        }

        $max = (int) $scores->first()->correct_count;
        $candidates = $scores->where('correct_count', $max)->values();
        $winner = $candidates->random();

        MiniGameWeeklyScore::query()
            ->whereDate('week_start', $weekStart->toDateString())
            ->whereDate('week_end', $weekEnd->toDateString())
            ->update(['is_winner' => false]);

        $winner->update(['is_winner' => true]);

        return $winner->fresh();
    }

    public function submitPayoutInfo(User $user, array $payload): MiniGamePayoutRequest
    {
        $winnerScore = MiniGameWeeklyScore::query()
            ->where('user_id', $user->id)
            ->where('is_winner', true)
            ->latest('week_end')
            ->first();

        if (!$winnerScore) {
            throw new \InvalidArgumentException('Ban chua co ket qua trung thuong de gui STK.');
        }

        return MiniGamePayoutRequest::updateOrCreate(
            [
                'user_id' => $user->id,
                'week_start' => $winnerScore->week_start->toDateString(),
                'week_end' => $winnerScore->week_end->toDateString(),
            ],
            [
                'bank_name' => $payload['bank_name'],
                'bank_account_name' => $payload['bank_account_name'],
                'bank_account_number' => $payload['bank_account_number'],
                'status' => 'submitted',
                'note' => $payload['note'] ?? null,
            ]
        );
    }

    public function normalizeNumbers(array $numbers): array
    {
        return collect($numbers)
            ->map(function ($number) {
                $digits = preg_replace('/\D/', '', (string) $number);
                if ($digits === '') {
                    return null;
                }
                return str_pad(substr($digits, -2), 2, '0', STR_PAD_LEFT);
            })
            ->filter(fn($number) => $number !== null)
            ->unique()
            ->values()
            ->all();
    }

    public function buildStatsFromPredictions(string $predictionDate): array
    {
        $predictions = MiniGamePrediction::query()
            ->whereDate('prediction_date', $predictionDate)
            ->get();

        $voteMap = [];
        foreach ($predictions as $prediction) {
            foreach (($prediction->numbers ?? []) as $number) {
                $voteMap[$number] = ($voteMap[$number] ?? 0) + 1;
            }
        }

        arsort($voteMap);
        $top = collect($voteMap)
            ->map(fn($votes, $number) => ['number' => $number, 'votes' => $votes])
            ->values();

        return [
            'total_users' => $predictions->count(),
            'leader' => $top->first() ?: null,
            'top_numbers' => $top->take(10)->all(),
            'predicted_numbers' => $top->all(),
        ];
    }

    public function cutoffAt(?Carbon $date = null): Carbon
    {
        $date = $date ?: now();
        return $date->copy()->setTime(17, 0, 0);
    }

    private function buildAiSuggestion(array $topNumbers): array
    {
        $topFour = collect($topNumbers)->take(4)->pluck('number')->values()->all();
        return [
            'provider' => 'local-rule-based',
            'numbers' => $topFour,
            'message' => 'Goi y AI tam thoi uu tien cac so co nhieu luot du doan.',
        ];
    }

    private function drawnNumbers(string $predictionDate): array
    {
        $result = Result::query()
            ->where('region', Number::REGION_MB)
            ->whereDate('date', $predictionDate)
            ->latest('id')
            ->first();

        if (!$result) {
            return [];
        }

        return Number::query()
            ->where('result_id', $result->id)
            ->pluck('number')
            ->map(function ($number) {
                $digits = preg_replace('/\D/', '', (string) $number);
                return str_pad(substr($digits, -2), 2, '0', STR_PAD_LEFT);
            })
            ->unique()
            ->values()
            ->all();
    }
}
