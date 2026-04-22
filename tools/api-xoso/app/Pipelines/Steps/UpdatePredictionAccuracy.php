<?php

namespace App\Pipelines\Steps;

use App\Models\Number;
use App\Models\Prediction;
use App\Models\Result;

class UpdatePredictionAccuracy
{
  public function handle($payload, $next)
  {
    $region = $payload['region'];
    $latestDate = $payload['latestDate'];

    $result = Result::where('date', $latestDate)
      ->where('region', $region)
      ->first();

    if (!$result) return $next($payload);

    $numbers = Number::where('result_id', $result->id)
      ->whereNotIn('prize', ['ma_db', 'db'])
      ->pluck('number')
      ->toArray();

    $predictions = Prediction::where('date', $latestDate)
      ->where('region', $region)
      ->whereIn('algorithm', ['ranking', 'vip_ranking'])
      ->get();

    foreach ($predictions as $p) {
      $pred = array_column($p->numbers, 'number');

      if (empty($pred)) continue;

      $hit = count(array_intersect($pred, $numbers));

      $p->accuracy = $hit / count($pred);
      $p->save();
    }

    $numbersDB = Number::where('result_id', $result->id)
      ->whereIn('prize', ['db'])
      ->pluck('number')
      ->first();

    $predictionsDB = Prediction::where('date', $latestDate)
      ->where('region', $region)
      ->whereIn('algorithm', ['db_ranking', 'vip_db_ranking'])
      ->get();

    foreach ($predictionsDB as $pDB) {
      $pred = array_column($pDB->numbers, 'number');

      if (empty($pred)) continue;

      $hit = in_array($numbersDB, $pred);

      $pDB->accuracy = $hit ? 1 : 0;
      $pDB->save();
    }

    return $next($payload);
  }
}
