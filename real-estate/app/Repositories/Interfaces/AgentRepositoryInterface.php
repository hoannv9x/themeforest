<?php

namespace App\Repositories\Interfaces;

use App\Models\Agent;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgentRepositoryInterface
{
  public function all(array $filters = [], array $sortBy = [], int $perPage = 15): LengthAwarePaginator;
}
