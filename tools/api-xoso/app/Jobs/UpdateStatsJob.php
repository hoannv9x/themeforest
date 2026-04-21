<?php

namespace App\Jobs;

use App\Models\Number;
use App\Models\NumberStat;
use App\Models\Prediction;
use App\Models\Result;
use App\Pipelines\StatsPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateStatsJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;

    public function handle()
    {
        $regions = Number::REGIONS;
        foreach ($regions as $region) {
            app(StatsPipeline::class)
                ->handle($region);
        }
    }
}
