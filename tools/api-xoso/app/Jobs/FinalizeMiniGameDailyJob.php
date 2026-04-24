<?php

namespace App\Jobs;

use App\Services\MiniGameService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FinalizeMiniGameDailyJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ?string $date = null)
    {
    }

    public function handle(MiniGameService $miniGameService): void
    {
        $date = $this->date ? Carbon::parse($this->date) : now();
        $miniGameService->finalizeDaily($date);
    }
}
