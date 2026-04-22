<?php

namespace App\Jobs;

use App\Models\CrawlLog;
use App\Models\Result;
use App\Models\Number;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

use function Symfony\Component\Clock\now;

class CrawlResultJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;
    protected $date;
    protected $page;
    protected $limit;

    public function __construct($date = null, $page = 1, $limit = 1)
    {
        $this->date = $date;
        $this->page = $page;
        $this->limit = $limit;
    }

    public function handle()
    {
        $regions = Number::REGIONS;

        foreach ($regions as $region) {
            if ($region != Number::REGION_MB) {
                continue;
            }

            try {
                $payload = $this->fetchFromApi($region);

                if (!$this->isValidPayload($payload)) {
                    Log::warning("API failed → fallback crawling...");
                    $payload = $this->fallbackCrawl($region);
                }

                if (empty($payload['data'])) {
                    throw new Exception('No data after fallback');
                }

                $this->store($payload['data']);
            } catch (Throwable $e) {
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

    protected function fetchFromApi($region)
    {
        $baseUrl = 'https://xosoapi.online';
        $apiKey = env('API_KEY_XOSO');

        $url = "$baseUrl/api/v1/vietnam/draws?region=$region";

        if ($this->date) {
            $url .= "&date=$this->date";
        }

        $url .= "&limit=" . $this->limit;

        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
        ])->get($url);

        return $response->json();
    }

    protected function isValidPayload($payload)
    {
        return !empty($payload)
            && empty($payload['error'])
            && !empty($payload['data']);
    }

    protected function fallbackCrawl($region)
    {
        if ($this->date) {
            return $this->crawlFromHtml($region);
        }

        return $this->crawlFromJs($region);
    }

    protected function crawlFromHtml($region)
    {
        $date = Carbon::parse($this->date)->format('d-m-Y');

        $url = "https://www.minhngoc.net.vn/ket-qua-xo-so/$date.html?mut=$region";

        $html = Http::get($url)->body();

        if (!$html) {
            throw new Exception("Cannot fetch HTML fallback");
        }

        // parse bằng DOM hoặc regex (khuyến nghị Symfony DomCrawler)
        return $this->parseHtml($html);
    }

    protected function crawlFromJs($region)
    {
        $names = [
            Number::REGION_MB => 'mien-bac',
            Number::REGION_MN => 'mien-nam',
            Number::REGION_MT => 'mien-trung',
        ];
        $url = "https://www.minhngoc.net.vn/getkqxs/$names[$region].js";

        $js = Http::get($url)->body();

        if (!$js) {
            throw new Exception("Cannot fetch JS fallback");
        }

        return $this->parseJs($js);
    }

    protected function parseJs($js)
    {
        preg_match_all("/append\\('(.+?)'\\);/s", $js, $matches);

        if (empty($matches[1])) {
            throw new Exception("Cannot extract HTML from JS");
        }

        // nối lại thành 1 block text
        $content = implode("\n", $matches[1]);

        // clean HTML entities + strip tag
        $text = html_entity_decode(strip_tags($content));

        return [
            'data' => [
                $this->mapTextToDrawFormat($text)
            ]
        ];
    }

    protected function parseHtml($html)
    {
        $crawler = new Crawler($html);

        // tùy structure HTML thực tế
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

        // lấy mã đặc biệt
        $firstNode = $crawler->filter('.loaive_content')->first();
        if ($firstNode->count()) {
            $maDB = explode('-', trim($firstNode->text()));
            $prizes['ma_db'] = $maDB;
        }

        // lấy các giải còn lại
        foreach ($maps as $class => $value) {
            $collection = $crawler->filter(".$class");

            if (!$collection->count()) {
                continue;
            }

            // lấy ngày đầu tiên
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
        $rawLines = preg_split('/\r\n|\r|\n|\t/', $text);
        $startKey = null;

        // Find the key of the line that starts with "Ngày:"
        foreach ($rawLines as $key => $line) {
            if (str_starts_with(trim($line), 'Ngày')) {
                $startKey = $key;
                break;
            }
        }

        // If "Ngày:" is found, take only lines from that key onward
        if ($startKey !== null) {
            $lines = array_slice($rawLines, $startKey);
        } else {
            $lines = $rawLines;
        }
        // Filter out empty lines
        $lines = array_values(array_filter(array_map('trim', $lines)));
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
                continue;
            }
            $line = $lines[$i];

            if (isset($map[$line])) {
                $key = $map[$line];

                // lấy dòng tiếp theo làm value
                $valueLine = $lines[$i + 1] ?? null;

                if (!$valueLine) continue;

                // split numbers
                $numbers = preg_split('/\s*-\s*/', $valueLine);

                // clean
                $numbers = array_values(array_filter(array_map('trim', $numbers)));

                $prizes[$key] = $numbers;
            }
        }


        if (empty($prizes)) {
            throw new Exception("Parse failed: no prizes found");
        }

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
        if (preg_match('/Ngày:\s*(\d{2}\/\d{2}\/\d{4})/', $text, $m)) {
            if (!empty($m[1])) {
                return Carbon::createFromFormat('d/m/Y', $m[1])->format('Y-m-d');
            }
        }

        // fallback
        return $this->date
            ? Carbon::parse($this->date)->format('Y-m-d')
            : now()->format('Y-m-d');
    }

    protected function store(array $draws)
    {
        foreach ($draws as $draw) {

            DB::beginTransaction();

            try {
                $date = Carbon::createFromFormat('Y-m-d', $draw['formatted_date']);

                $province = $draw['province'];
                $provinceCode = $province['code'];
                $provinceName = $province['name'];
                $regionCode   = $province['region']['code'];

                // 🔒 chống duplicate (nên có unique index DB)
                $exists = Result::where('date', $date)
                    ->where('province_code', $provinceCode)
                    ->where('region', $regionCode)
                    ->exists();

                if ($exists) {
                    Log::info("Duplicate skipped", [
                        'date' => $date,
                        'province' => $provinceCode,
                        'region' => $regionCode
                    ]);

                    DB::commit();
                    continue;
                }

                // 💾 insert result
                $result = Result::create([
                    'date' => $date,
                    'province_code' => $provinceCode,
                    'province_name' => $provinceName,
                    'region' => $regionCode,
                    'raw_data' => $draw['prizes'],
                    'created_at' => now(),
                ]);

                // 🔢 build numbers
                $numbers = [];

                foreach ($draw['prizes'] as $prize => $values) {
                    foreach ($values as $value) {

                        $numbers[] = [
                            'result_id'  => $result->id,
                            'number'     => substr($value, -2), // 2 số cuối
                            'raw_number' => $value,
                            'prize'      => $prize
                        ];
                    }
                }

                // ⚡ insert batch
                if (!empty($numbers)) {
                    Number::insert($numbers);
                }

                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();

                Log::error("Store failed", [
                    'draw' => $draw,
                    'error' => $e->getMessage()
                ]);

                throw $e;
            }
        }
    }
}
