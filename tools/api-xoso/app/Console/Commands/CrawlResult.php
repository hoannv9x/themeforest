<?php

namespace App\Console\Commands;

use App\Jobs\CrawlResultJob;
use Illuminate\Console\Command;

class CrawlResult extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl-result {date?} {--page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->argument('date');
        $page = $this->option('page');
        $this->info("Crawl result for date: $date, page: $page");
        dispatch(new CrawlResultJob($date, $page))->onQueue('crawl-result');
    }
}
