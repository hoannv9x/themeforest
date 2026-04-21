<?php
namespace App\Pipelines\Steps;

use App\Models\Number;

class LoadData
{
  public function handle($payload, $next)
  {
    $region = $payload['region'];

    $data = Number::query()
      ->join('results', 'results.id', '=', 'numbers.result_id')
      ->where('results.region', $region)
      ->whereNotIn('numbers.prize', ['ma_db'])
      ->orderBy('results.date')
      ->get([
        'numbers.number',
        'numbers.prize',
        'results.date'
      ]);

    $latestDate = $data->max('date');

    $payload['data'] = $data;
    $payload['latestDate'] = $latestDate;

    return $next($payload);
  }
}
