<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NumberStatsService;
use Illuminate\Http\Request;

class NumberStatController extends Controller
{
    protected $numberStatsService;

    public function __construct(NumberStatsService $numberStatsService)
    {
        $this->numberStatsService = $numberStatsService;
    }

    public function index(Request $request)
    {
        $region = $request->input('region');
        $day = $request->input('day');

        $numbers = $this->numberStatsService->getMostFrequentNumbers($region, $day);

        return response()->json($numbers);
    }
}
