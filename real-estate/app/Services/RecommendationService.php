<?php

namespace App\Services;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RecommendationService
{
    public function getRecommendationsForUser(User $user, int $limit = 5): Collection
    {
        // Example: Simple content-based recommendation based on user's favorite properties
        $favoritePropertyIds = $user->favorites()->pluck('property_id')->toArray();

        if (empty($favoritePropertyIds)) {
            // If no favorites, return popular or featured properties
            return Property::where('status', 'active')->orderByDesc('views_count')->limit($limit)->get();
        }

        // Get property types and cities from user's favorite properties
        $favoriteProperties = Property::whereIn('id', $favoritePropertyIds)->get();
        $propertyTypeIds = $favoriteProperties->pluck('property_type_id')->unique()->toArray();
        $cityIds = $favoriteProperties->pluck('city_id')->unique()->toArray();

        $recommendations = Property::where('status', 'active')
            ->whereNotIn('id', $favoritePropertyIds) // Don't recommend already favorited
            ->where(function ($query) use ($propertyTypeIds, $cityIds) {
                $query->whereIn('property_type_id', $propertyTypeIds)
                      ->orWhereIn('city_id', $cityIds);
            })
            ->inRandomOrder() // Simple randomization
            ->limit($limit)
            ->get();

        if ($recommendations->isEmpty()) {
            // Fallback if no specific recommendations found
            return Property::where('status', 'active')->orderByDesc('views_count')->limit($limit)->get();
        }

        return $recommendations;
    }

    public function getSimilarProperties(Property $property, int $limit = 5): Collection
    {
        // Example: Recommend properties with similar type and city
        return Property::where('id', '!=', $property->id)
            ->where('status', 'active')
            ->where('property_type_id', $property->property_type_id)
            ->where('city_id', $property->city_id)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}