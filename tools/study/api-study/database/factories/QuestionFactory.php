<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['single_choice', 'true_false']),
            'content' => fake()->sentence() . '?',
            'explanation' => fake()->paragraph(),
            'points' => 10,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
