<?php

namespace App\Console\Commands;

use App\Jobs\UpdateStatsJob;
use Illuminate\Console\Command;

class UpdateStat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-stat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new UpdateStatsJob())->onQueue('update-stats');
    }
}
