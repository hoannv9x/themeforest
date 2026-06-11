<?php

namespace Database\Factories;

use App\Models\QuizResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizResult>
 */
class QuizResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'score' => fake()->numberBetween(1, 100),
            'total_points' => 100,
            'user_answers' => json_encode([
                '1' => 'A',
                '2' => 'B',
                '3' => 'C',
            ]),
            'completed_at' => now(),
        ];
    }
}
