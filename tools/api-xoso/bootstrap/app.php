<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Jobs\CrawlResultJob;
use App\Jobs\EvaluateMiniGameWeeklyJob;
use App\Jobs\FinalizeMiniGameDailyJob;
use App\Jobs\GeneratePredictionJob;
use App\Jobs\SeedMiniGamePredictionsJob;
use App\Jobs\UpdateStatsJob;
use Carbon\Carbon;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new CrawlResultJob(date: Carbon::today()->format('Y-m-d')))->daily('18:35');
        $schedule->job(new UpdateStatsJob())->daily('19:00');
        $schedule->job(new GeneratePredictionJob())->daily('00:00');
        $schedule->job(new SeedMiniGamePredictionsJob(30, Carbon::today()->format('Y-m-d')))->daily('08:00');
        $schedule->job(new FinalizeMiniGameDailyJob(Carbon::today()->format('Y-m-d')))->daily('17:00');
        $schedule->job(new EvaluateMiniGameWeeklyJob())->weeklyOn(1, '20:00');
    })
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
