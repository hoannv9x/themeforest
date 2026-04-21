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
    protected $signature = 'app:crawl-result {date?} {--page=1} {--limit=1}';

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
        $limit = $this->option('limit');
        $this->info("Crawl result for date: $date, page: $page, limit: $limit");
        dispatch(new CrawlResultJob($date, $page, $limit))->onQueue('crawl-result');
    }
}
