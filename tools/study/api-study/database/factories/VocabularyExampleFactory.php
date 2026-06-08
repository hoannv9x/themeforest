<?php

namespace Database\Factories;

use App\Models\VocabularyExample;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VocabularyExample>
 */
class VocabularyExampleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'example_sentence' => fake()->sentence(),
            'translation' => fake()->sentence(),
        ];
    }
}
