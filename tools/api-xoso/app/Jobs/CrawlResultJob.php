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
        $baseUrl = 'https://xosoapi.online';
        $apiKey = env('API_KEY_XOSO');
        $url = '';
        foreach ($regions as $region) {
            try {
                $regionCode = $region;
                $ch = curl_init();
                $url = "$baseUrl/api/v1/vietnam/draws?region=$regionCode";
                if ($this->date !== null) {
                    $url .= "&date=$this->date";
                }
                if ($this->page > 1) {
                    $url .= "&page=$this->page";
                }
                $url .= "&limit=" . $this->limit;
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        "X-API-Key: $apiKey",
                    ],
                ]);
                $response = curl_exec($ch);
                curl_close($ch);
                $payload = json_decode($response, true);

                if (empty($payload)) {
                    CrawlLog::create([
                        'source' => $url,
                        'status' => 'failed',
                        'message' => 'API trả về dữ liệu trống',
                        'created_at' => now(),
                    ]);
                    continue;
                }

                if (!$payload['success']) {
                    CrawlLog::create([
                        'source' => $url,
                        'status' => 'failed',
                        'message' => 'API trả về thất bại',
                        'created_at' => now(),
                    ]);
                    continue;
                }

                if (empty($payload['data'])) {
                    CrawlLog::create([
                        'source' => $url,
                        'status' => 'failed',
                        'message' => 'Data trống',
                        'created_at' => now(),
                    ]);
                    continue;
                }

                DB::beginTransaction();
                Log::info("Crawl $regionCode {$this->date}: ", $payload['data']);
                foreach ($payload['data'] as $draw) {
                    $date = Carbon::createFromFormat('d/m/Y', $draw['formatted_date'])
                        ->format('Y-m-d');
                    $province = $draw['province'];

                    $provinceCode = $province['code'];
                    $provinceName = $province['name'];
                    $regionCode   = $province['region']['code'];

                    // ❗ chống duplicate theo date + province
                    $exists = Result::where('date', $date)
                        ->where('province_code', $provinceCode)
                        ->where('region', $regionCode)
                        ->exists();

                    if ($exists) {
                        Log::info("Duplicate $date $provinceCode $regionCode {$this->date}");
                        continue;
                    }

                    // 💾 lưu result
                    $result = Result::create([
                        'date' => $date,
                        'province_code' => $provinceCode,
                        'province_name' => $provinceName,
                        'region' => $regionCode,
                        'raw_data' => $draw['prizes'],
                        'created_at' => now(),
                    ]);

                    Log::info("Crawl: ", ['result' => $result]);

                    // 🔢 parse numbers
                    $numbers = [];

                    foreach ($draw['prizes'] as $prize => $values) {
                        foreach ($values as $value) {

                            $numbers[] = [
                                'result_id' => $result->id,
                                'number' => substr($value, -2),
                                'raw_number' => $value,
                                'prize' => $prize
                            ];
                        }
                    }

                    // ⚡ insert batch
                    Number::insert($numbers);
                }

                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();
                CrawlLog::create([
                    'source' => $url,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                    'created_at' => now(),
                ]);
                throw $e;
            }
        }
    }
}
