<?php

namespace App\Console\Commands;

use App\Jobs\FinalizeMiniGameDailyJob;
use Illuminate\Console\Command;

class FinalizeMiniGameDaily extends Command
{
    protected $signature = 'app:mini-game-finalize-daily {--date=}';

    protected $description = 'Finalize mini game predictions and evaluate hits for a date';

    public function handle(): void
    {
        $date = $this->option('date') ?: now()->toDateString();
        dispatch(new FinalizeMiniGameDailyJob($date))->onQueue('mini-game');
        $this->info("Queued finalize mini game daily job for {$date}");
    }
}
