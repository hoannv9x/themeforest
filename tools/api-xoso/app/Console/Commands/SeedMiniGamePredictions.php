<?php

namespace App\Console\Commands;

use App\Jobs\SeedMiniGamePredictionsJob;
use Illuminate\Console\Command;

class SeedMiniGamePredictions extends Command
{
    protected $signature = 'app:mini-game-seed-predictions {--count=50} {--date=}';

    protected $description = 'Seed random mini game predictions from system bots';

    public function handle(): void
    {
        $count = (int) $this->option('count');
        $date = $this->option('date') ?: now()->toDateString();

        dispatch(new SeedMiniGamePredictionsJob($count, $date))->onQueue('mini-game');

        $this->info("Queued mini game seed job with count={$count}, date={$date}");
    }
}
