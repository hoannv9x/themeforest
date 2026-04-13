<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NumberStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        return NumberStat::selectRaw('number, MAX(current_gap) as current_gap, Max(total_count) as total_count')
            ->groupBy('number')
            ->orderByDesc('current_gap')
            ->get();
        // return Cache::remember('stats', 600, function () {
        // });
    }

    public function detail(Request $request, int $number)
    {
        return response(NumberStat::where('number', $number)->first());
    }
}
