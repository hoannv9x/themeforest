<?php

namespace App\Repositories\Eloquent;

use App\Models\Property;
use App\Repositories\Interfaces\PropertyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PropertyRepository implements PropertyRepositoryInterface
{
    public function all(array $filters = [], array $sortBy = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Property::query()->with(['city', 'agent', 'images']);

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
            $query->latest('published_at'); // Default sort
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Property
    {
        return Property::with(['user', 'agent', 'propertyType', 'city', 'district', 'images', 'reviews'])->find($id);
    }

    public function findBySlugOrId(string $slugOrId): ?Property
    {
        return Property::with([
            'user',
            'agent',
            'propertyType',
            'city',
            'district',
            'images',
            'reviews',
            'agent.user',
            'rooms',
            'rooms.hotspots',
            'rooms.image'
        ])
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->first();
    }

    public function create(array $data): Property
    {
        return Property::create($data);
    }

    public function update(int $id, array $data): ?Property
    {
        $property = $this->find($id);
        if ($property) {
            $property->update($data);
        }
        return $property;
    }

    public function delete(int $id): bool
    {
        return Property::destroy($id);
    }

    public function search(string $keyword, array $filters = [], array $sortBy = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Property::query()
            ->where('title', 'like', '%' . $keyword . '%')
            ->orWhere('description', 'like', '%' . $keyword . '%')
            ->orWhere('address', 'like', '%' . $keyword . '%');

        // Apply additional filters
        if (isset($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }
        if (isset($filters['property_type_id'])) {
            $query->where('property_type_id', $filters['property_type_id']);
        }
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply sorting
        if (!empty($sortBy)) {
            foreach ($sortBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        } else {
            $query->latest('published_at'); // Default sort
        }

        return $query->paginate($perPage);
    }

    public function getFeaturedProperties(int $limit = 5): Collection
    {
        return Property::where('is_featured', true)
            ->where('status', 'active')
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function getRelatedProperties(Property $property, int $limit = 4): Collection
    {
        return Property::where('id', '!=', $property->id)
            ->where(function ($query) use ($property) {
                $query->where('city_id', $property->city_id)
                    ->orWhere('property_type_id', $property->property_type_id);
            })
            ->where('status', 'active')
            ->with(['city', 'propertyType'])
            ->limit($limit)
            ->get();
    }
}
