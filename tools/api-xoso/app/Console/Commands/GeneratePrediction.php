<?php

namespace App\Console\Commands;

use App\Jobs\GeneratePredictionJob;
use Illuminate\Console\Command;

class GeneratePrediction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-prediction';

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
        dispatch(new GeneratePredictionJob())->onQueue('generate-prediction');
    }
}
