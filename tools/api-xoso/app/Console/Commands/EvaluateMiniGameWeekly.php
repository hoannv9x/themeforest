<?php

namespace App\Console\Commands;

use App\Jobs\EvaluateMiniGameWeeklyJob;
use Illuminate\Console\Command;

class EvaluateMiniGameWeekly extends Command
{
    protected $signature = 'app:mini-game-evaluate-weekly {--start=} {--end=}';

    protected $description = 'Evaluate weekly mini game scores and pick winner';

    public function handle(): void
    {
        $start = $this->option('start');
        $end = $this->option('end');
        dispatch(new EvaluateMiniGameWeeklyJob($start, $end))->onQueue('mini-game');
        $this->info('Queued weekly mini game evaluation job.');
    }
}
