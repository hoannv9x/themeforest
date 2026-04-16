<?php

namespace App\Jobs;

use App\Models\Number as ModelsNumber;
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
        // $data = $service->generate();
        $algorithms = [
            'gan' => $service->getGan(ModelsNumber::REGION_MB),
            'trend' => $service->getTrend(ModelsNumber::REGION_MB),
            'roi' => $service->getRoi(ModelsNumber::REGION_MB),
            'ai' => $service->getAI(ModelsNumber::REGION_MB),
        ];

        foreach ($algorithms as $code => $data) {
            Prediction::updateOrCreate(
                [
                    'date' => now()->toDateString(),
                    'region' => ModelsNumber::REGION_MB,
                    'algorithm' => $code,
                ],
                [
                    'algorithm' => $code,
                    'numbers' => $data,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Prediction::updateOrCreate(
        //     [
        //         'date' => today(),
        //         'region' => 'mb'
        //     ],
        //     [
        //         'numbers' => $data['numbers'],
        //         'meta' => $data['meta'],
        //         'algorithm' => 'weighted_v2',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]
        // );
    }
}
