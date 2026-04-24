<?php

namespace App\Jobs;

use App\Models\MiniGamePrediction;
use App\Models\Prediction;
use App\Services\MiniGameService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SeedMiniGamePredictionsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $count = 50,
        private readonly ?string $date = null
    ) {}

    public function handle(MiniGameService $miniGameService): void
    {
        $date = $this->date ?: now()->toDateString();
        $pool = $this->buildNumberPool($date);
        if (empty($pool)) {
            $pool = range(0, 99);
        }

        for ($i = 1; $i <= $this->count; $i++) {
            $pickCount = random_int(3, 5);
            $numbers = collect($pool)
                ->shuffle()
                ->take($pickCount)
                ->map(fn($number) => str_pad((string) $number, 2, '0', STR_PAD_LEFT))
                ->values()
                ->all();

            MiniGamePrediction::updateOrCreate(
                [
                    'prediction_date' => $date,
                    'source' => 'system',
                    'predictor_name' => 'system-bot-' . $i,
                ],
                [
                    'numbers' => $miniGameService->normalizeNumbers($numbers),
                    'user_id' => $i,
                ]
            );
        }
    }

    private function buildNumberPool(string $date): array
    {
        $prediction = Prediction::query()
            ->where('region', 'mb')
            ->whereDate('date', $date)
            ->where('algorithm', 'ranking')
            ->first();

        if (!$prediction) {
            return [];
        }

        $base = collect($prediction->numbers ?? [])
            ->pluck('number')
            ->filter()
            ->map(fn($number) => (int) $number)
            ->values()
            ->all();

        if (empty($base)) {
            return [];
        }

        $expanded = [];
        foreach ($base as $num) {
            $expanded[] = $num;
            $expanded[] = $num;
            $expanded[] = ($num + random_int(1, 9)) % 100;
        }

        return $expanded;
    }
}
