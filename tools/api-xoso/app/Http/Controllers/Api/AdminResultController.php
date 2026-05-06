<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateStatsJob;
use App\Models\Number;
use App\Models\Result;
use App\Services\WebhookDispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminResultController extends Controller
{
    public function index(Request $request)
    {
        $query = Result::query()->orderByDesc('date');

        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        return response()->json(
            $query->paginate(20)
        );
    }

    public function showByDate(string $date, Request $request)
    {
        $region = (string) ($request->input('region') ?: Number::REGION_MB);

        $result = Result::query()
            ->where('region', $region)
            ->whereDate('date', $date)
            ->latest('id')
            ->with('numbers')
            ->first();

        if (!$result) {
            return response()->json([
                'date' => $date,
                'region' => $region,
                'result' => null,
            ]);
        }

        return response()->json([
            'result' => $result,
        ]);
    }

    public function upsertByDate(string $date, Request $request)
    {
        $payload = $request->validate([
            'region' => ['required', 'in:' . implode(',', Number::REGIONS)],
            'province_code' => ['nullable', 'string', 'max:50'],
            'raw_data' => ['required', 'array'],
        ]);

        foreach ($payload['raw_data'] as $prize => $values) {
            if (!is_array($values)) {
                return response()->json([
                    'message' => "raw_data[$prize] must be an array",
                ], 422);
            }
        }

        $region = $payload['region'];
        $provinceCode = $payload['province_code'] ?? Number::MB_TRADITION;

        $result = Result::query()
            ->where('region', $region)
            ->whereDate('date', $date)
            ->where('province_code', $provinceCode)
            ->latest('id')
            ->first();

        DB::beginTransaction();

        try {
            if (!$result) {
                $result = Result::create([
                    'date' => $date,
                    'region' => $region,
                    'province_code' => $provinceCode,
                    'raw_data' => $payload['raw_data'],
                    'created_at' => now(),
                ]);
            } else {
                $result->raw_data = $payload['raw_data'];
                $result->save();
            }

            Number::query()->where('result_id', $result->id)->delete();

            $numbers = [];
            foreach ($payload['raw_data'] as $prize => $values) {
                foreach ($values as $value) {
                    $raw = preg_replace('/\D/', '', (string) $value);
                    $last2 = $raw === '' ? null : str_pad(substr($raw, -2), 2, '0', STR_PAD_LEFT);
                    if ($last2 === null) {
                        continue;
                    }

                    $numbers[] = [
                        'result_id' => $result->id,
                        'number' => $last2,
                        'raw_number' => (string) $value,
                        'prize' => (string) $prize,
                    ];
                }
            }

            if (!empty($numbers)) {
                Number::insert($numbers);
            }

            app(WebhookDispatchService::class)->dispatchResultUpdated($result->fresh('numbers'));
            dispatch(new UpdateStatsJob())->onQueue('update-stats');

            DB::commit();

            return response()->json([
                'message' => 'Saved',
                'result' => $result->fresh('numbers'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

