<?php

namespace App\Jobs;

use App\Models\CrawlLog;
use App\Models\Number;
use App\Models\TempResult;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class CrawlTempResultJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;

    protected ?string $date;

    public function __construct(?string $date = null)
    {
        $this->date = $date;
    }

    public function handle(): void
    {
        $targetDate = $this->date
            ? Carbon::parse($this->date)->format('Y-m-d')
            : Carbon::today(config('app.timezone'))->format('Y-m-d');

        $region = Number::REGION_MB;
        $provinceCode = Number::MB_TRADITION;

        $row = TempResult::query()->firstOrCreate(
            ['date' => $targetDate, 'region' => $region, 'province_code' => $provinceCode],
            ['raw_data' => null, 'is_complete' => false, 'attempts' => 0]
        );

        if ($row->is_complete) {
            return;
        }

        try {
            $draw = $this->crawlFromHtml($region, $targetDate);
            if (!$draw) {
                $nextAttempts = (int) $row->attempts + 1;
                $row->forceFill([
                    'attempts' => $nextAttempts,
                    'last_fetched_at' => now(),
                ])->save();

                if ($this->shouldContinue($nextAttempts)) {
                    dispatch(new self($targetDate))
                        ->delay(now()->addMinutes(2))
                        ->onQueue('crawl-temp-result');
                }

                return;
            }

            if (empty($draw['prizes']) || !is_array($draw['prizes'])) {
                throw new Exception('Invalid draw format');
            }

            $prizes = $draw['prizes'];
            logger()->info('prizes: ', $prizes);
            $isComplete = $this->isCompleteDayData($prizes);

            $nextAttempts = (int) $row->attempts + 1;
            $row->forceFill([
                'raw_data' => $prizes,
                'attempts' => $nextAttempts,
                'last_fetched_at' => now(),
                'is_complete' => $isComplete,
                'completed_at' => $isComplete ? now() : null,
            ])->save();

            if (!$isComplete && $this->shouldContinue($nextAttempts)) {
                dispatch(new self($targetDate))
                    ->delay(now()->addMinutes(2))
                    ->onQueue('crawl-temp-result');
            }
        } catch (Throwable $e) {
            $nextAttempts = (int) $row->attempts + 1;
            $row->forceFill([
                'attempts' => $nextAttempts,
                'last_fetched_at' => now(),
            ])->save();

            if ($this->shouldContinue($nextAttempts)) {
                dispatch(new self($targetDate))
                    ->delay(now()->addMinutes(2))
                    ->onQueue('crawl-temp-result');
            }

            CrawlLog::create([
                'source' => 'crawl_temp_result_job',
                'status' => 'failed',
                'message' => $e->getMessage(),
                'created_at' => now(),
            ]);

            throw $e;
        }
    }

    protected function shouldContinue(int $attempts): bool
    {
        if ($attempts >= 60) {
            return false;
        }

        $now = Carbon::now(config('app.timezone'));
        $end = $now->copy()->setTime(19, 00, 0);
        return $now->lessThan($end);
    }

    protected function isCompleteDayData(array $prizes): bool
    {
        $expectedCounts = [
            'db' => 1,
            'g1' => 1,
            'g2' => 2,
            'g3' => 6,
            'g4' => 4,
            'g5' => 6,
            'g6' => 3,
            'g7' => 4,
        ];

        foreach ($expectedCounts as $key => $expected) {
            if (!array_key_exists($key, $prizes)) {
                return false;
            }
            if (!is_array($prizes[$key]) || count(array_filter($prizes[$key], fn($v) => $v !== '')) < $expected) {
                return false;
            }

            $valid = collect($prizes[$key])
                ->filter(fn ($v) => is_string($v) && preg_match('/^\d+$/', $v))
                ->count();

            if ($valid < $expected) {
                return false;
            }
        }

        return true;
    }

    protected function isValidPayload($payload): bool
    {
        return !empty($payload)
            && empty($payload['error'])
            && !empty($payload['data']);
    }

    protected function crawlFromHtml(string $region, string $date): ?array
    {
        $dmy = Carbon::parse($date)->format('d-m-Y');

        $url = "https://www.minhngoc.net.vn/ket-qua-xo-so/{$dmy}.html?mut={$region}";

        $html = Http::get($url)->body();

        if (!$html) {
            throw new Exception("Cannot fetch HTML");
        }

        $prizes = $this->parseHtmlIfTargetDate($html, $date);
        if ($prizes === null) {
            return null;
        }

        return [
            'formatted_date' => Carbon::parse($date)->format('Y-m-d'),
            'province' => [
                'code' => Number::MB_TRADITION,
                'name' => 'Miền Bắc',
                'region' => ['code' => Number::REGION_MB],
            ],
            'prizes' => $prizes,
        ];
    }

    protected function parseHtmlIfTargetDate(string $html, string $targetDate): ?array
    {
        $crawler = new Crawler($html);

        $pageTitleNode = $crawler->filter('.pagetitle')->first();
        if (!$pageTitleNode->count()) {
            return null;
        }

        $pageTitle = trim(html_entity_decode($pageTitleNode->text()));
        $pageDate = $this->extractDateFromText($pageTitle);
        if ($pageDate === null) {
            return null;
        }

        $target = Carbon::parse($targetDate)->format('Y-m-d');
        if ($pageDate !== $target) {
            return null;
        }

        $prizes = [];
        $maps = [
            'giaidb' => 'db',
            'giai1' => 'g1',
            'giai2' => 'g2',
            'giai3' => 'g3',
            'giai4' => 'g4',
            'giai5' => 'g5',
            'giai6' => 'g6',
            'giai7' => 'g7',
        ];

        $firstNode = $crawler->filter('.loaive_content')->first();
        if ($firstNode->count()) {
            $maDB = explode('-', trim($firstNode->text()));
            $prizes['ma_db'] = $maDB;
        }

        foreach ($maps as $class => $value) {
            $collection = $crawler->filter(".$class");
            if (!$collection->count()) {
                continue;
            }

            $node = $collection->eq(0);
            $values = [];
            $node->filter('div')->each(function ($item) use (&$values) {
                $text = trim(html_entity_decode($item->text()));
                if ($text !== '') {
                    $values[] = $text;
                }
            });
            $prizes[$value] = $values;
        }

        if (empty($prizes)) {
            return null;
        }

        return $prizes;
    }

    protected function extractDateFromText(string $text): ?string
    {
        if (preg_match('/(\d{2})[\/\-](\d{2})[\/\-](\d{4})/', $text, $m)) {
            return Carbon::createFromFormat('d/m/Y', "{$m[1]}/{$m[2]}/{$m[3]}")->format('Y-m-d');
        }

        return null;
    }
}
