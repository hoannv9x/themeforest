<?php

namespace App\Jobs;

use App\Models\Prediction;
use App\Services\PredictionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeneratePredictionJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(PredictionService $service)
    {
        $data = $service->generate();

        Prediction::updateOrCreate(
            [
                'date' => today(),
                'region' => 'mb'
            ],
            [
                'numbers' => $data['numbers'],
                'meta' => $data['meta'],
                'algorithm' => 'weighted_v2'
            ]
        );
    }
}
