<?php

namespace App\Jobs;

use App\Models\Number as ModelsNumber;
use App\Models\NumberStat;
use App\Models\Prediction;
use App\Services\ExplainService;
use App\Services\PredictionService;
use App\Services\ScoreService;
use App\Services\ScoreServiceVip;
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
    public function handle(ScoreService $service, ScoreServiceVip $vipService, ExplainService $explainService)
    {
        $stats = NumberStat::where('region', ModelsNumber::REGION_MB)->get();
        $topDb = $vipService->rankDb($stats)->take(5);

        $explainsDb = $topDb->map(
            fn($item) =>
            $explainService->explainDb((object)$item)
        );

        $top = $vipService->rank($stats)->take(5);

        $explains = $top->map(
            fn($item) =>
            $explainService->explainLoto((object)$item)
        );
        $algorithms = [
            'ranking' => $service->rank($stats),
            'vip_ranking' => $explains,
            'vip_db_ranking' => $explainsDb,
            'db_ranking' => $service->rankDb($stats),
        ];

        foreach ($algorithms as $code => $data) {
            Prediction::updateOrCreate(
                [
                    'date' => now()->toDateString(),
                    'region' => ModelsNumber::REGION_MB,
                    'algorithm' => $code,
                ],
                [
                    'numbers' => $data,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
