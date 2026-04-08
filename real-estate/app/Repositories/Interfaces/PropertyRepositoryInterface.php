<?php

namespace App\Repositories\Interfaces;

use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PropertyRepositoryInterface
{
    public function all(array $filters = [], array $sortBy = [], int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Property;
    public function create(array $data): Property;
    public function update(int $id, array $data): ?Property;
    public function delete(int $id): bool;
    public function search(string $keyword, array $filters = [], array $sortBy = [], int $perPage = 15): LengthAwarePaginator;
    public function getFeaturedProperties(int $limit = 5): Collection;
    public function getRelatedProperties(Property $property, int $limit = 4): Collection;
    public function findBySlugOrId(string $slugOrId): ?Property;
}
