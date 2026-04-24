<?php

namespace App\Jobs;

use App\Mail\MiniGameWinnerMail;
use App\Services\MiniGameService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class EvaluateMiniGameWeeklyJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ?string $weekStart = null, private readonly ?string $weekEnd = null)
    {
    }

    public function handle(MiniGameService $miniGameService): void
    {
        $weekStart = $this->weekStart
            ? Carbon::parse($this->weekStart)->startOfDay()
            : now()->subWeek()->startOfWeek();
        $weekEnd = $this->weekEnd
            ? Carbon::parse($this->weekEnd)->endOfDay()
            : now()->subWeek()->endOfWeek();

        $miniGameService->computeWeeklyScores($weekStart, $weekEnd);
        $winner = $miniGameService->pickWeeklyWinner($weekStart, $weekEnd);

        if (!$winner || !$winner->user?->email) {
            return;
        }

        Mail::to($winner->user->email)->queue(
            new MiniGameWinnerMail($winner->user, $winner)
        );
    }
}
