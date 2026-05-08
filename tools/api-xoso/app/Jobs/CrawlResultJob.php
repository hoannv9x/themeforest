<?php

namespace App\Jobs;

use App\Models\CrawlLog;
use App\Models\Result;
use App\Models\Number;
use App\Models\Prediction;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;
use App\Services\WebhookDispatchService;

use function Symfony\Component\Clock\now;

class CrawlResultJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;
    protected $date;
    protected $page;
    protected $limit;
    protected $attempts;
    protected $forceUpdate;

    public function __construct($date = null, $page = 1, $limit = 1, $attempts = 0, $forceUpdate = false)
    {
        $this->date = $date;
        $this->page = $page;
        $this->limit = $limit;
        $this->attempts = (int) $attempts;
        $this->forceUpdate = (bool) $forceUpdate;
        Log::debug("CrawlResultJob initialized", [
            'date' => $this->date,
            'page' => $this->page,
            'limit' => $this->limit,
            'attempts' => $this->attempts,
            'force_update' => $this->forceUpdate,
        ]);
    }

    public function handle()
    {
        $regions = Number::REGIONS;
        Log::debug("Processing regions", ['all_regions' => $regions]);

        foreach ($regions as $region) {
            if ($region != Number::REGION_MB) {
                Log::debug("Skipping non-MB region", ['region' => $region]);
                continue;
            }
            Log::info("Crawling $region...", ['region' => $region, 'job_date' => $this->date]);

            try {
                $targetDate = $this->date
                    ? Carbon::parse($this->date)->format('Y-m-d')
                    : Carbon::today(config('app.timezone'))->format('Y-m-d');

                Log::debug("Attempting to fetch from primary API", ['region' => $region]);
                $payload = $this->fetchFromApi($region);
                Log::debug("Primary API response received", ['payload' => $payload]);

                if (!$this->isValidPayload($payload)) {
                    Log::warning("API failed → fallback crawling...", [
                        'region' => $region,
                        'payload_errors' => $payload['error'] ?? 'no payload'
                    ]);
                    $payload = $this->fallbackCrawl($region);
                    Log::debug("Fallback crawl completed", ['payload' => $payload]);
                }

                if (empty($payload['data'])) {
                    Log::error("No data received after fallback", ['region' => $region, 'payload' => $payload]);
                    throw new Exception('No data after fallback');
                }

                $firstDraw = $payload['data'][0] ?? null;
                $drawDate = is_array($firstDraw) ? ($firstDraw['formatted_date'] ?? null) : null;
                $prizes = is_array($firstDraw) ? ($firstDraw['prizes'] ?? null) : null;

                if (!is_string($drawDate) || $drawDate !== $targetDate || !is_array($prizes)) {
                    $this->scheduleRetry($targetDate);
                    Log::info("CrawlResultJob retry scheduled due to invalid/mismatched draw date", [
                        'region' => $region,
                        'target_date' => $targetDate,
                        'draw_date' => $drawDate,
                        'attempts' => $this->attempts,
                    ]);
                    return;
                }

                $isComplete = $this->isCompleteDayData($prizes);
                $hasDb = $this->hasDbPrize($prizes);

                if (!$isComplete && !$hasDb) {
                    $this->scheduleRetry($targetDate);
                    Log::info("CrawlResultJob retry scheduled (not complete and no DB yet)", [
                        'region' => $region,
                        'target_date' => $targetDate,
                        'attempts' => $this->attempts,
                        'keys' => array_keys($prizes),
                    ]);
                    return;
                }

                Log::debug("Starting to store crawled data", ['data_count' => count($payload['data'])]);
                $this->store($payload['data']);
                Log::info("Successfully processed region", ['region' => $region]);
            } catch (Throwable $e) {
                Log::error("Crawl job failed completely", [
                    'region' => $region,
                    'error_message' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString()
                ]);

                CrawlLog::create([
                    'source' => 'crawl_result_job',
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                    'created_at' => now(),
                ]);

                throw $e;
            }
        }
    }

    protected function scheduleRetry(string $targetDate): void
    {
        $nextAttempts = (int) $this->attempts + 1;
        if (!$this->shouldContinue($nextAttempts)) {
            Log::warning("CrawlResultJob retry stopped (limits reached)", [
                'target_date' => $targetDate,
                'attempts' => $nextAttempts,
            ]);
            return;
        }

        dispatch(new self($targetDate, $this->page, $this->limit, $nextAttempts))
            ->delay(120)
            ->onQueue('crawl-result');
    }

    protected function shouldContinue(int $attempts): bool
    {
        if ($attempts >= 60) {
            return false;
        }

        $now = Carbon::now(config('app.timezone'));
        $end = $now->copy()->setTime(23, 30, 0);
        return $now->lessThan($end);
    }

    protected function hasDbPrize(array $prizes): bool
    {
        if (!array_key_exists('db', $prizes)) {
            return false;
        }
        if (!is_array($prizes['db']) || count($prizes['db']) < 1) {
            return false;
        }
        $value = $prizes['db'][0] ?? null;
        if (!is_string($value)) {
            return false;
        }
        return preg_match('/^\d+$/', $value) === 1;
    }

    protected function isCompleteDayData(array $prizes): bool
    {
        $expectedCounts = $this->expectedPrizeCounts();

        foreach ($expectedCounts as $key => $expected) {
            if (!array_key_exists($key, $prizes)) {
                return false;
            }
            if (!is_array($prizes[$key]) || count($prizes[$key]) < $expected) {
                return false;
            }
            $valid = collect($prizes[$key])
                ->filter(fn($v) => is_string($v) && preg_match('/^\d+$/', $v))
                ->count();
            if ($valid < $expected) {
                return false;
            }
        }

        return true;
    }

    protected function expectedPrizeCounts(): array
    {
        return [
            'db' => 1,
            'g1' => 1,
            'g2' => 2,
            'g3' => 6,
            'g4' => 4,
            'g5' => 6,
            'g6' => 3,
            'g7' => 4,
        ];
    }

    protected function isResultCompleteById(int $resultId): bool
    {
        $counts = Number::query()
            ->where('result_id', $resultId)
            ->selectRaw('prize, COUNT(*) as c')
            ->groupBy('prize')
            ->pluck('c', 'prize');

        foreach ($this->expectedPrizeCounts() as $prize => $expected) {
            if (((int) ($counts[$prize] ?? 0)) < $expected) {
                return false;
            }
        }
        return true;
    }

    protected function fetchFromApi($region)
    {
        $baseUrl = 'https://xosoapi.online';
        $apiKey = env('API_KEY_XOSO');
        Log::debug("API configuration loaded", [
            'base_url' => $baseUrl,
            'api_key_provided' => !empty($apiKey),
            'region' => $region
        ]);

        $url = "$baseUrl/api/v1/vietnam/draws?region=$region";

        if ($this->date) {
            $url .= "&date=$this->date";
        }

        $url .= "&limit=" . $this->limit;
        Log::debug("Making API request", ['url' => $url]);

        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
        ])->get($url);

        Log::debug("API response received", [
            'status_code' => $response->status(),
            'success' => $response->successful()
        ]);

        return $response->json();
    }

    protected function isValidPayload($payload)
    {
        $isValid = !empty($payload)
            && empty($payload['error'])
            && !empty($payload['data']);

        Log::debug("Validating payload", ['is_valid' => $isValid, 'payload' => $payload]);
        return $isValid;
    }

    protected function fallbackCrawl($region)
    {
        Log::debug("Starting fallback crawl", [
            'region' => $region,
            'has_date' => !empty($this->date)
        ]);

        if ($this->date) {
            return $this->crawlFromHtml($region);
        }

        return $this->crawlFromJs($region);
    }

    protected function crawlFromHtml($region)
    {
        $date = Carbon::parse($this->date)->format('d-m-Y');
        $url = "https://www.minhngoc.net.vn/ket-qua-xo-so/$date.html?mut=$region";
        Log::debug("Starting HTML crawl", ['url' => $url, 'region' => $region, 'date' => $date]);

        $html = Http::get($url)->body();

        if (!$html) {
            Log::error("Failed to fetch HTML content", ['url' => $url]);
            throw new Exception("Cannot fetch HTML fallback");
        }

        Log::debug("HTML content fetched successfully", ['content_length' => strlen($html)]);
        return $this->parseHtml($html);
    }

    protected function crawlFromJs($region)
    {
        $names = [
            Number::REGION_MB => 'mien-bac',
            Number::REGION_MN => 'mien-nam',
            Number::REGION_MT => 'mien-trung',
        ];
        $url = "https://www.minhngoc.net.vn/getkqxs/{$names[$region]}.js";
        Log::debug("Starting JS crawl", ['url' => $url, 'region' => $region]);

        $js = Http::get($url)->body();

        if (!$js) {
            Log::error("Failed to fetch JS content", ['url' => $url]);
            throw new Exception("Cannot fetch JS fallback");
        }

        Log::debug("JS content fetched successfully", ['content_length' => strlen($js)]);
        return $this->parseJs($js);
    }

    protected function parseJs($js)
    {
        Log::debug("Parsing JS content");
        preg_match_all("/append\\('(.+?)'\\);/s", $js, $matches);

        if (empty($matches[1])) {
            Log::error("Failed to extract HTML from JS", ['js_snippet' => substr($js, 0, 500)]);
            throw new Exception("Cannot extract HTML from JS");
        }

        $content = implode("\n", $matches[1]);
        $text = html_entity_decode(strip_tags($content));
        Log::debug("JS parsing completed", ['extracted_text_length' => strlen($text)]);

        return [
            'data' => [
                $this->mapTextToDrawFormat($text)
            ]
        ];
    }

    protected function parseHtml($html)
    {
        Log::debug("Parsing HTML content");
        $crawler = new Crawler($html);

        $prizes = [];

        $maps = [
            'giaidb' => 'db',
            'giai1'  => 'g1',
            'giai2'  => 'g2',
            'giai3'  => 'g3',
            'giai4'  => 'g4',
            'giai5'  => 'g5',
            'giai6'  => 'g6',
            'giai7'  => 'g7',
        ];

        $firstNode = $crawler->filter('.loaive_content')->first();
        if ($firstNode->count()) {
            $maDB = explode('-', trim($firstNode->text()));
            $prizes['ma_db'] = $maDB;
            Log::debug("Extracted special prize", ['ma_db' => $maDB]);
        } else {
            Log::warning("Special prize node not found", ['selector' => '.loaive_content']);
        }

        foreach ($maps as $class => $value) {
            $collection = $crawler->filter(".$class");

            if (!$collection->count()) {
                Log::warning("Prize class not found", ['class' => $class]);
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
            Log::debug("Extracted prize values", ['prize' => $value, 'values' => $values]);
        }

        Log::debug("HTML parsing completed", ['total_prizes_extracted' => count($prizes)]);
        return [
            'data' => [[
                'formatted_date' => Carbon::parse($this->date)->format('Y-m-d'),
                'province' => [
                    'code' => Number::MB_TRADITION,
                    'name' => 'Miền Bắc',
                    'region' => ['code' => Number::REGION_MB]
                ],
                'prizes' => $prizes
            ]]
        ];
    }

    protected function mapTextToDrawFormat($text)
    {
        Log::debug("Mapping raw text to draw format");
        $rawLines = preg_split('/\r\n|\r|\n|\t/', $text);
        $startKey = null;

        foreach ($rawLines as $key => $line) {
            if (str_starts_with(trim($line), 'Ngày')) {
                $startKey = $key;
                break;
            }
        }

        if ($startKey !== null) {
            $lines = array_slice($rawLines, $startKey);
            Log::debug("Found start of date section", ['start_line' => $startKey]);
        } else {
            $lines = $rawLines;
            Log::warning("Date section marker 'Ngày:' not found in raw text");
        }

        $lines = array_values(array_filter(array_map('trim', $lines)));
        Log::debug("Processed input lines", ['total_lines' => count($lines), 'lines' => $lines]);

        $map = [
            'Giải ĐB'   => 'db',
            'Giải nhất' => 'g1',
            'Giải nhì'  => 'g2',
            'Giải ba'   => 'g3',
            'Giải tư'   => 'g4',
            'Giải năm'  => 'g5',
            'Giải sáu'  => 'g6',
            'Giải bảy'  => 'g7',
        ];

        $prizes = [];
        $date = null;
        for ($i = 0; $i < count($lines); $i++) {
            if ($i === 0) {
                $date = $this->extractDate($line);
                Log::debug("Extracted date from first line", ['raw_line' => $lines[0], 'formatted_date' => $date]);
                continue;
            }
            $line = $lines[$i];

            if (isset($map[$line])) {
                $key = $map[$line];
                $valueLine = $lines[$i + 1] ?? null;

                if (!$valueLine) {
                    Log::warning("No values found for prize", ['prize' => $line, 'line_index' => $i]);
                    continue;
                }

                $numbers = preg_split('/\s*-\s*/', $valueLine);
                $numbers = array_values(array_filter(array_map('trim', $numbers)));

                $prizes[$key] = $numbers;
                Log::debug("Extracted numbers for prize", ['prize' => $key, 'numbers' => $numbers]);
            }
        }

        if (empty($prizes)) {
            Log::error("No prizes could be parsed from text", ['raw_text' => substr($text, 0, 1000)]);
            throw new Exception("Parse failed: no prizes found");
        }

        Log::debug("Text mapping completed", ['total_prizes' => count($prizes), 'extracted_date' => $date]);
        return [
            'formatted_date' => $date,
            'province' => [
                'code' => Number::MB_TRADITION,
                'name' => 'Miền Bắc',
                'region' => ['code' => Number::REGION_MB],
            ],
            'prizes' => $prizes,
        ];
    }

    protected function extractDate($text)
    {
        Log::debug("Extracting date from text", ['input_text' => $text]);
        if (preg_match('/Ngày:\s*(\d{2}\/\d{2}\/\d{4})/', $text, $m)) {
            if (!empty($m[1])) {
                $formatted = Carbon::createFromFormat('d/m/Y', $m[1])->format('Y-m-d');
                Log::debug("Date extracted successfully", ['raw_date' => $m[1], 'formatted_date' => $formatted]);
                return $formatted;
            }
        }

        if ($this->date) {
            $formatted = Carbon::parse($this->date)->format('Y-m-d');
            Log::warning("Regex date extraction failed, using job date", ['job_date' => $this->date, 'formatted_date' => $formatted]);
            return $formatted;
        }

        $formatted = now()->format('Y-m-d');
        Log::warning("Date extraction failed, using current date", ['formatted_date' => $formatted]);
        return $formatted;
    }

    protected function store(array $draws)
    {
        Log::debug("Starting storage process", ['total_draws' => count($draws)]);
        foreach ($draws as $drawIndex => $draw) {
            Log::debug("Processing draw", ['draw_index' => $drawIndex, 'draw_data' => $draw]);

            DB::beginTransaction();
            Log::debug("Database transaction started");

            try {
                $date = Carbon::createFromFormat('Y-m-d', $draw['formatted_date']);
                $province = $draw['province'];
                $provinceCode = $province['code'];
                $provinceName = $province['name'];
                $regionCode   = $province['region']['code'];

                Log::debug("Checking for existing result", [
                    'date' => $date->toDateString(),
                    'province_code' => $provinceCode,
                    'region' => $regionCode
                ]);

                $existing = Result::where('date', $date)
                    ->where('province_code', $provinceCode)
                    ->where('region', $regionCode)
                    ->latest('id')
                    ->first();

                if ($existing) {
                    if (!$this->forceUpdate) {
                        Log::info("Duplicate skipped", [
                            'date' => $date,
                            'province' => $provinceCode,
                            'region' => $regionCode
                        ]);

                        DB::commit();
                        Log::debug("Transaction committed for duplicate");
                        continue;
                    }

                    if ($this->isResultCompleteById($existing->id)) {
                        Log::info("Existing result complete, skipped update", [
                            'date' => $date,
                            'province' => $provinceCode,
                            'region' => $regionCode,
                            'result_id' => $existing->id,
                        ]);

                        DB::commit();
                        Log::debug("Transaction committed for complete existing result");
                        continue;
                    }

                    Log::info("Updating incomplete existing result", [
                        'date' => $date,
                        'province' => $provinceCode,
                        'region' => $regionCode,
                        'result_id' => $existing->id,
                    ]);

                    $existing->raw_data = $draw['prizes'];
                    $existing->save();

                    Number::where('result_id', $existing->id)->delete();

                    $numbers = [];
                    foreach ($draw['prizes'] as $prize => $values) {
                        foreach ($values as $value) {
                            $numbers[] = [
                                'result_id'  => $existing->id,
                                'number'     => substr($value, -2),
                                'raw_number' => $value,
                                'prize'      => $prize
                            ];
                        }
                    }

                    if (!empty($numbers)) {
                        Number::insert($numbers);
                        Log::debug("Numbers batch inserted", ['total_numbers' => count($numbers)]);
                    }

                    Log::debug("Dispatching webhook for updated result", ['result_id' => $existing->id]);
                    app(WebhookDispatchService::class)->dispatchResultUpdated($existing);

                    DB::commit();
                    Log::debug("Transaction committed successfully (updated existing)");

                    Log::debug("Updating prediction hits", [
                        'region' => $regionCode,
                        'date' => $date->toDateString(),
                        'result_id' => $existing->id
                    ]);
                    $this->updatePredictionHitsForDate($regionCode, $date->toDateString(), $existing->id);
                    continue;
                }

                Log::debug("Creating new result record");
                $result = Result::create([
                    'date' => $date,
                    'province_code' => $provinceCode,
                    'province_name' => $provinceName,
                    'region' => $regionCode,
                    'raw_data' => $draw['prizes'],
                    'created_at' => now(),
                ]);
                Log::debug("Result created successfully", ['result_id' => $result->id]);

                $numbers = [];
                foreach ($draw['prizes'] as $prize => $values) {
                    foreach ($values as $value) {
                        $numbers[] = [
                            'result_id'  => $result->id,
                            'number'     => substr($value, -2),
                            'raw_number' => $value,
                            'prize'      => $prize
                        ];
                    }
                }

                if (!empty($numbers)) {
                    Number::insert($numbers);
                    Log::debug("Numbers batch inserted", ['total_numbers' => count($numbers)]);
                }

                Log::debug("Dispatching webhook for new result", ['result_id' => $result->id]);
                app(WebhookDispatchService::class)->dispatchResultUpdated($result);

                DB::commit();
                Log::debug("Transaction committed successfully");

                Log::debug("Updating prediction hits", [
                    'region' => $regionCode,
                    'date' => $date->toDateString(),
                    'result_id' => $result->id
                ]);
                $this->updatePredictionHitsForDate($regionCode, $date->toDateString(), $result->id);
            } catch (Throwable $e) {
                DB::rollBack();
                Log::error("Store failed", [
                    'draw' => $draw,
                    'error' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString()
                ]);

                throw $e;
            }
        }
        Log::debug("Storage process completed for all draws");
    }

    private function updatePredictionHitsForDate(string $region, string $date, int $resultId): void
    {
        Log::debug("Starting prediction hit update", [
            'region' => $region,
            'date' => $date,
            'result_id' => $resultId
        ]);

        $allDrawnNumbers = Number::query()
            ->where('result_id', $resultId)
            ->pluck('number')
            ->map(fn($number) => $this->normalizeTwoDigits($number))
            ->filter()
            ->unique()
            ->values();
        Log::debug("All drawn two-digit numbers", ['numbers' => $allDrawnNumbers->toArray()]);

        $dbDrawnNumbers = Number::query()
            ->where('result_id', $resultId)
            ->where('prize', 'db')
            ->pluck('number')
            ->map(fn($number) => $this->normalizeTwoDigits($number))
            ->filter()
            ->unique()
            ->values();
        Log::debug("Special prize (DB) two-digit numbers", ['numbers' => $dbDrawnNumbers->toArray()]);

        $dbRaw = Number::query()
            ->where('result_id', $resultId)
            ->where('prize', 'db')
            ->pluck('raw_number')
            ->first();
        $dbThreeDigits = $this->normalizeThreeDigits($dbRaw);
        Log::debug("Special prize three-digit number", ['raw' => $dbRaw, 'normalized' => $dbThreeDigits]);

        $predictions = Prediction::query()
            ->where('region', $region)
            ->whereDate('date', $date)
            ->whereIn('algorithm', ['ranking', 'vip_ranking', 'db_ranking', 'vip_db_ranking', 'vip_3_cang', 'vip_bach_thu'])
            ->get();
        Log::debug("Found predictions to update", ['total_predictions' => $predictions->count()]);

        foreach ($predictions as $prediction) {
            Log::debug("Processing prediction", [
                'prediction_id' => $prediction->id,
                'algorithm' => $prediction->algorithm
            ]);

            $numbers = $prediction->numbers ?? [];
            if (!is_array($numbers)) {
                Log::warning("Invalid numbers format in prediction", ['prediction_id' => $prediction->id]);
                continue;
            }

            $isDbAlgo = in_array($prediction->algorithm, ['db_ranking', 'vip_db_ranking'], true);
            $isThreeCang = $prediction->algorithm === 'vip_3_cang';

            $hitSet = $isDbAlgo ? $dbDrawnNumbers : $allDrawnNumbers;

            $updated = array_map(function ($item) use ($hitSet, $isThreeCang, $dbThreeDigits) {
                if (!is_array($item)) {
                    return $item;
                }

                $num = $item['number'] ?? null;
                if ($num === null) {
                    $item['is_hit'] = false;
                    return $item;
                }

                if ($isThreeCang) {
                    $item['is_hit'] = $dbThreeDigits !== null && $this->normalizeThreeDigits($num) === $dbThreeDigits;
                    Log::debug("3-digit prediction hit check", [
                        'number' => $num,
                        'normalized' => $this->normalizeThreeDigits($num),
                        'target' => $dbThreeDigits,
                        'is_hit' => $item['is_hit']
                    ]);
                    return $item;
                }

                $two = $this->normalizeTwoDigits($num);
                $item['is_hit'] = $two !== null && $hitSet->contains($two);
                Log::debug("Two-digit prediction hit check", [
                    'number' => $num,
                    'normalized' => $two,
                    'targets' => $hitSet->toArray(),
                    'is_hit' => $item['is_hit']
                ]);
                return $item;
            }, $numbers);

            $prediction->numbers = $updated;
            $prediction->save();
            Log::debug("Prediction updated successfully", ['prediction_id' => $prediction->id]);
        }
        Log::debug("All predictions updated for this result");
    }

    private function normalizeTwoDigits($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $value);
        if ($digits === '') {
            return null;
        }

        return str_pad(substr($digits, -2), 2, '0', STR_PAD_LEFT);
    }

    private function normalizeThreeDigits($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $value);
        if ($digits === '') {
            return null;
        }

        return str_pad(substr($digits, -3), 3, '0', STR_PAD_LEFT);
    }
}
