<?php

namespace App\Pipelines\Steps;

class GroupData
{
  public function handle($payload, $next)
  {
    $normal = [];
    $db = [];

    foreach ($payload['data'] as $row) {
      if ($row->prize === 'db') {
        $db[$row->number][] = $row->date;
      } else {
        $normal[$row->number][] = $row->date;
      }
    }

    $payload['normal'] = $normal;
    $payload['db'] = $db;

    return $next($payload);
  }
}
