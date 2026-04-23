<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Number;
use App\Models\Prediction;
use App\Services\PredictionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use function Symfony\Component\Clock\now;

class PredictionController extends Controller
{
    protected $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    public function today()
    {
        $key = 'predictions:' . Carbon::now()->format('Y-m-d');
        if (Cache::has($key)) {
            return Cache::get($key);
        }

        return Cache::remember($key, Carbon::now()->endOfDay(), function () {
            return $this->predictionService->getPredictionPro(Number::REGION_MB);
        });
    }

    public function todayVip()
    {
        $key = 'predictions:vip:' . Carbon::now()->format('Y-m-d');
        if (Cache::has($key)) {
            return Cache::get($key);
        }

        return Cache::remember($key, Carbon::now()->endOfDay(), function () {
            return $this->predictionService->getPredictionPro(Number::REGION_MB, true);
        });
    }
}
