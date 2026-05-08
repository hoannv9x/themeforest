<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Number;
use App\Services\PredictionService;

class PredictionController extends Controller
{
    public function __construct(private readonly PredictionService $predictionService)
    {
    }

    public function today()
    {
        return $this->predictionService->getPredictionPro(Number::REGION_MB, false);
    }

    public function todayVip()
    {
        return $this->predictionService->getPredictionPro(Number::REGION_MB, true);
    }

    public function yesterday()
    {
        return $this->predictionService->getYesterdayPredictionWithHits(Number::REGION_MB, false);
    }

    public function yesterdayVip()
    {
        return $this->predictionService->getYesterdayPredictionWithHits(Number::REGION_MB, true);
    }
}
