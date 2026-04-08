<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyImage>
 */
class PropertyImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'image_path' => 'properties/' . fake()->image(null, 640, 480, 'house', false),
            'image_host' => fake()->domainName(),
            'caption' => fake()->sentence(),
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
