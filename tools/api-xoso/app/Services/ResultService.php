<?php

namespace App\Services;

use App\Models\Number;
use App\Models\Prediction;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ResultService
{
  public function index($request, bool $forceFull = false)
  {
    Log::debug("Index results", ['request' => $request]);
    $query = Result::query();
    $yearStart = Carbon::now(config('app.timezone'))->startOfYear()->format('Y-m-d');

    if ($request->is_multi_region) {
      return $query->with('numbers')
        ->whereIn('region', Number::REGIONS)
        ->where('date', '>=', Carbon::now()->subDays(5)->format('Y-m-d'))
        ->orderBy('date', 'desc')
        ->get(['region', 'date', 'raw_data'])
        ->groupBy('region')
        ->map(function ($items) {
          return $items->mapWithKeys(function ($item) {
            return [
              $item->date->format('Y-m-d') => $item->raw_data
            ];
          });
        });
    }

    $includePredictions = filter_var($request->include_predictions, FILTER_VALIDATE_BOOLEAN);
    if ($includePredictions) {
      $query->with('numbers');
    }

    $paginator = $query
      ->when(!$forceFull, fn($q) => $q->whereDate('date', '>=', $yearStart))
      ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
      ->when($request->region, fn($q) => $q->where('region', $request->region))
      ->orderByDesc('date')
      ->paginate(20);

    if (!$includePredictions) {
      return $paginator;
    }

    $items = collect($paginator->items());
    $dates = $items
      ->map(fn($r) => $r->date?->format('Y-m-d'))
      ->filter()
      ->unique()
      ->values()
      ->all();
    $regions = $items
      ->map(fn($r) => $r->region)
      ->filter()
      ->unique()
      ->values()
      ->all();

    $predictionRows = Prediction::query()
      ->whereIn('region', $regions)
      ->whereIn('date', $dates)
      ->whereIn('algorithm', ['ranking', 'db_ranking'])
      ->get(['region', 'date', 'algorithm', 'numbers'])
      ->groupBy(fn($p) => $p->region . '|' . Carbon::parse($p->date)->format('Y-m-d'));

    $items->each(function ($result) use ($predictionRows) {
      $key = $result->region . '|' . $result->date?->format('Y-m-d');
      $rows = $predictionRows->get($key, collect());

      $ranking = optional($rows->firstWhere('algorithm', 'ranking'))->numbers ?? [];
      $dbRanking = optional($rows->firstWhere('algorithm', 'db_ranking'))->numbers ?? [];

      $predLoto = collect($ranking)
        ->map(fn($i) => $this->normalizeTwoDigits($i['number'] ?? null))
        ->filter()
        ->unique()
        ->values()
        ->all();
      $predDb = collect($dbRanking)
        ->map(fn($i) => $this->normalizeTwoDigits($i['number'] ?? null))
        ->filter()
        ->unique()
        ->values()
        ->all();

      $allDrawn = collect($result->numbers ?? [])
        ->map(fn($n) => $this->normalizeTwoDigits($n->number ?? null))
        ->filter()
        ->unique()
        ->values()
        ->all();
      $dbDrawn = collect($result->numbers ?? [])
        ->filter(fn($n) => ($n->prize ?? null) === 'db')
        ->map(fn($n) => $this->normalizeTwoDigits($n->number ?? null))
        ->filter()
        ->unique()
        ->values()
        ->all();

      $result->setAttribute('prediction', [
        'loto_numbers' => $predLoto,
        'db_numbers' => $predDb,
      ]);
      $result->setAttribute('prediction_hits', [
        'loto_numbers' => array_values(array_intersect($predLoto, $allDrawn)),
        'db_numbers' => array_values(array_intersect($predDb, $dbDrawn)),
      ]);

      $result->unsetRelation('numbers');
    });

    return $paginator;
  }

  public function show($request, string $date, bool $forceFull = false)
  {
    $yearStart = Carbon::now(config('app.timezone'))->startOfYear()->format('Y-m-d');
    if (!$forceFull && Carbon::parse($date)->format('Y-m-d') < $yearStart) {
      return response()->json(['message' => 'Free chỉ xem kết quả trong năm nay'], 403);
    }

    $region = $request->region ?: Number::REGION_MB;
    $result = Result::query()
      ->when($region, fn($q) => $q->where('region', $region))
      ->whereDate('date', $date)
      ->with('numbers')
      ->latest('id')
      ->first();

    if (!$result) {
      return response()->json(['message' => 'Không tìm thấy kết quả'], 404);
    }

    $rows = Prediction::query()
      ->where('region', $result->region)
      ->whereDate('date', Carbon::parse($date)->format('Y-m-d'))
      ->whereIn('algorithm', ['ranking', 'db_ranking'])
      ->get(['algorithm', 'numbers']);

    $ranking = optional($rows->firstWhere('algorithm', 'ranking'))->numbers ?? [];
    $dbRanking = optional($rows->firstWhere('algorithm', 'db_ranking'))->numbers ?? [];

    $predLoto = collect($ranking)
      ->map(fn($i) => $this->normalizeTwoDigits($i['number'] ?? null))
      ->filter()
      ->unique()
      ->values()
      ->all();
    $predDb = collect($dbRanking)
      ->map(fn($i) => $this->normalizeTwoDigits($i['number'] ?? null))
      ->filter()
      ->unique()
      ->values()
      ->all();

    $allDrawn = collect($result->numbers ?? [])
      ->map(fn($n) => $this->normalizeTwoDigits($n->number ?? null))
      ->filter()
      ->unique()
      ->values()
      ->all();
    $dbDrawn = collect($result->numbers ?? [])
      ->filter(fn($n) => ($n->prize ?? null) === 'db')
      ->map(fn($n) => $this->normalizeTwoDigits($n->number ?? null))
      ->filter()
      ->unique()
      ->values()
      ->all();

    return response()->json([
      'result' => [
        'date' => $result->date?->format('Y-m-d'),
        'region' => $result->region,
        'province_code' => $result->province_code,
        'raw_data' => $result->raw_data,
      ],
      'prediction' => [
        'loto_numbers' => $predLoto,
        'db_numbers' => $predDb,
      ],
      'prediction_hits' => [
        'loto_numbers' => array_values(array_intersect($predLoto, $allDrawn)),
        'db_numbers' => array_values(array_intersect($predDb, $dbDrawn)),
      ],
    ]);
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
}
