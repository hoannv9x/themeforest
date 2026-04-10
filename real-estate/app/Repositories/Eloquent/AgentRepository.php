<?php

namespace App\Repositories\Eloquent;

use App\Models\Agent;
use App\Repositories\Interfaces\AgentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AgentRepository implements AgentRepositoryInterface
{
  public function all(array $filters = [], array $sortBy = [], int $perPage = 15): LengthAwarePaginator
  {
    $query = Agent::query()->withCount(['properties']);

    // Apply filters using when
    $query->when(isset($filters['city_id']), fn($q) => $q->where('city_id', $filters['city_id']))
      ->when(isset($filters['property_type_id']), fn($q) => $q->where('property_type_id', $filters['property_type_id']))
      ->when(isset($filters['district_id']), fn($q) => $q->where('district_id', $filters['district_id']))
      ->when(isset($filters['min_price']), fn($q) => $q->where('price', '>=', $filters['min_price']))
      ->when(isset($filters['max_price']), fn($q) => $q->where('price', '<=', $filters['max_price']))
      ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
      ->when(isset($filters['keyword']), fn($q) => $q->where('title', 'like', '%' . $filters['keyword'] . '%'));

    // Apply sorting
    if (!empty($sortBy)) {
      foreach ($sortBy as $column => $direction) {
        $query->orderBy($column, $direction);
      }
    } else {
      $query->latest('id');
    }

    return $query->paginate($perPage);
  }

  public function find(int $id): Agent|null
  {
    return Agent::find($id);
  }
}
