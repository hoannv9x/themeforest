<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Number;
use App\Models\Prediction;
use App\Services\PredictionService;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    protected $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    public function today()
    {
        return $this->predictionService->getPredictionPro(Number::REGION_MB);
    }
}
