<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\City;
use App\Models\District;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        return [
            'user_id' => User::factory(),
            'agent_id' => Agent::factory(),
            'property_type_id' => PropertyType::factory(),
            'city_id' => City::factory(),
            'district_id' => function (array $attributes) {
                return District::factory()->create(['city_id' => $attributes['city_id']])->id;
            },
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->numberBetween(100000, 5000000),
            'currency' => 'USD',
            'address' => fake()->address(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'bedrooms' => fake()->numberBetween(1, 10),
            'bathrooms' => fake()->numberBetween(1, 5),
            'area_sqft' => fake()->numberBetween(500, 5000),
            'lot_size_sqft' => fake()->numberBetween(1000, 10000),
            'year_built' => fake()->year(),
            'status' => fake()->randomElement(['pending', 'active', 'sold', 'rented', 'inactive']),
            'is_featured' => fake()->boolean(),
            'views_count' => fake()->numberBetween(0, 1000),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
