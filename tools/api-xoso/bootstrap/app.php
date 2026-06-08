<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Jobs\CrawlResultJob;
use App\Jobs\CrawlTempResultJob;
use App\Jobs\EvaluateMiniGameWeeklyJob;
use App\Jobs\FinalizeMiniGameDailyJob;
use App\Jobs\GeneratePredictionJob;
use App\Jobs\SeedMiniGamePredictionsJob;
use App\Jobs\UpdateStatsJob;
use Carbon\Carbon;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new CrawlTempResultJob(Carbon::today(config('app.timezone'))->format('Y-m-d')))->at('18:05');
        $schedule->job(new CrawlResultJob(date: Carbon::today(config('app.timezone'))->format('Y-m-d')))->at('18:35');
        $schedule->job(new UpdateStatsJob())->at('18:45');
        $schedule->job(new GeneratePredictionJob())->at('19:00');
        $schedule->job(new SeedMiniGamePredictionsJob(30, Carbon::today(config('app.timezone'))->format('Y-m-d')))->at('08:00');
        $schedule->job(new FinalizeMiniGameDailyJob(Carbon::today(config('app.timezone'))->format('Y-m-d')))->at('17:00');
        $schedule->job(new EvaluateMiniGameWeeklyJob())->weeklyOn(1, '20:00');
        $schedule->command('app:generate-prediction-snapshots')->at('20:00');
        // $schedule->command('pulse:check')->everyMinute();
    })
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
