<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Jobs\CrawlResultJob;
use App\Jobs\GeneratePredictionJob;
use App\Jobs\UpdateStatsJob;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new CrawlResultJob)->daily('18:35');
        $schedule->job(new UpdateStatsJob)->daily('19:00');
        $schedule->job(new GeneratePredictionJob)->daily('00:00');
    })
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
