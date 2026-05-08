<?php

namespace App\Console\Commands;

use App\Jobs\CrawlResultJob;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RequeueIncompleteResults extends Command
{
    protected $signature = 'app:requeue-incomplete-results 
        {--start_date= : YYYY-MM-DD}
        {--end_date= : YYYY-MM-DD}
        {--region=mb : mb|mt|mn}
        {--queue=crawl-result : queue name}
        {--dry-run : chỉ in danh sách, không dispatch}';

    protected $description = 'Tìm các ngày có result thiếu giải và enqueue CrawlResultJob để crawl/update lại';

    public function handle()
    {
        $region = (string) $this->option('region');
        $queue = (string) $this->option('queue');
        $dryRun = (bool) $this->option('dry-run');

        $end = $this->option('end_date')
            ? Carbon::parse((string) $this->option('end_date'))->toDateString()
            : now()->toDateString();
        $start = $this->option('start_date')
            ? Carbon::parse((string) $this->option('start_date'))->toDateString()
            : Carbon::parse($end)->subDays(30)->toDateString();

        $startObj = Carbon::parse($start)->startOfDay();
        $endObj = Carbon::parse($end)->startOfDay();
        if ($endObj->lessThan($startObj)) {
            [$startObj, $endObj] = [$endObj, $startObj];
            [$start, $end] = [$end, $start];
        }

        $this->info("Scan incomplete results: region={$region}, range={$start}..{$end}");

        $latestResults = Result::query()
            ->select(['date', DB::raw('MAX(id) as id')])
            ->where('region', $region)
            ->whereDate('date', '>=', $startObj->toDateString())
            ->whereDate('date', '<=', $endObj->toDateString())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($latestResults->isEmpty()) {
            $this->info('No results found in range.');
            return self::SUCCESS;
        }

        $resultIds = $latestResults->pluck('id')->filter()->values();
        $countRows = DB::table('numbers')
            ->select(['result_id', 'prize', DB::raw('COUNT(*) as c')])
            ->whereIn('result_id', $resultIds)
            ->groupBy('result_id', 'prize')
            ->get();

        $countsByResult = [];
        foreach ($countRows as $row) {
            $countsByResult[(int) $row->result_id][(string) $row->prize] = (int) $row->c;
        }

        $expected = [
            'db' => 1,
            'g1' => 1,
            'g2' => 2,
            'g3' => 6,
            'g4' => 4,
            'g5' => 6,
            'g6' => 3,
            'g7' => 4,
        ];

        $incompleteDates = [];
        foreach ($latestResults as $r) {
            $rid = (int) $r->id;
            $counts = $countsByResult[$rid] ?? [];
            $ok = true;
            foreach ($expected as $prize => $min) {
                if (((int) ($counts[$prize] ?? 0)) < $min) {
                    $ok = false;
                    break;
                }
            }
            if (!$ok) {
                $incompleteDates[] = (string) $r->date;
            }
        }

        $incompleteDates = array_values(array_unique($incompleteDates));

        if (empty($incompleteDates)) {
            $this->info('No incomplete days detected.');
            return self::SUCCESS;
        }

        $this->warn('Incomplete dates:');
        foreach ($incompleteDates as $d) {
            $this->line("- {$d}");
        }

        if ($dryRun) {
            $this->info('Dry-run enabled. No jobs dispatched.');
            return self::SUCCESS;
        }

        foreach ($incompleteDates as $d) {
            dispatch(new CrawlResultJob($d, 1, 1, 0, true))->onQueue($queue);
        }

        $this->info('Dispatched CrawlResultJob (force update) for incomplete dates.');
        return self::SUCCESS;
    }
}

