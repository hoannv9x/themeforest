<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->city();
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'state' => fake()->state(),
            'country' => 'Vietnam',
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
