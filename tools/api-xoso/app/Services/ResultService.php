<?php

namespace App\Services;

use App\Models\Number;
use App\Models\NumberStat;
use App\Models\Result;
use Carbon\Carbon;

class ResultService
{
  public function index($request)
  {
    $query = Result::query();

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

    return $query
      ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
      ->when($request->region, fn($q) => $q->where('region', $request->region))
      ->latest()
      ->paginate(20);
  }
}
