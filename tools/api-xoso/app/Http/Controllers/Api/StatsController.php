<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Number;
use App\Models\NumberStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function vipIndex(Request $request)
    {
        $region = $request->query('region', Number::REGION_MB);
        return response()->json(
            NumberStat::query()
                ->where('region', $region)
                ->orderByDesc('current_gap')
                ->get()
        );
    }

    public function vipDetail(Request $request, int $number)
    {
        $region = $request->query('region', Number::REGION_MB);
        $num = str_pad((string) $number, 2, '0', STR_PAD_LEFT);
        return response()->json(
            NumberStat::query()->where('region', $region)->where('number', $num)->first()
        );
    }

    public function index(Request $request)
    {
        $region = $request->query('region', Number::REGION_MB);

        return response()->json(
            NumberStat::query()
                ->where('region', $region)
                ->select(['number', 'region', 'total_count', 'last_appeared_at', 'current_gap', 'max_gap'])
                ->orderByDesc('current_gap')
                ->get()
        );
    }

    public function detail(Request $request, int $number)
    {
        $region = $request->query('region', Number::REGION_MB);
        $num = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

        $stat = NumberStat::query()
            ->where('region', $region)
            ->where('number', $num)
            ->select(['number', 'region', 'total_count', 'last_appeared_at', 'current_gap', 'max_gap'])
            ->first();

        if (!$stat) {
            return response()->json(['message' => 'Không tìm thấy thông tin'], 404);
        }

        return response()->json($stat);
    }
}
