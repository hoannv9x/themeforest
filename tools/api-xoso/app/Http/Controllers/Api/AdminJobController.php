<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\CrawlResultJob;
use App\Jobs\GeneratePredictionJob;
use App\Jobs\UpdateStatsJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class AdminJobController extends Controller
{
    public function runDailyPipeline(Request $request)
    {
        $payload = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = $payload['start_date'] ?? now()->toDateString();
        $endDate = $payload['end_date'] ?? $startDate;

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        if ($end->lessThan($start)) {
            [$start, $end] = [$end, $start];
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $dates = collect();
        $cursor = $start->copy();
        while ($cursor->lessThanOrEqualTo($end)) {
            $dates->push($cursor->toDateString());
            $cursor->addDay();
        }

        $jobs = $dates
            ->map(fn($date) => new CrawlResultJob($date, 1, 1))
            ->values()
            ->all();

        $jobs[] = new UpdateStatsJob();
        $jobs[] = new GeneratePredictionJob();

        Bus::chain($jobs)->dispatch();

        return response()->json([
            'message' => 'Đã chạy pipeline: crawl-result -> update-stats -> generate-prediction',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dates' => $dates->values(),
        ]);
    }
}
