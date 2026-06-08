<?php

namespace Database\Factories;

use App\Models\Vocabulary;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vocabulary>
 */
class VocabularyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $word = fake()->word() . ' ' . fake()->unique()->numberBetween(1, 10000);
        return [
            'word' => $word,
            'slug' => Str::slug($word),
            'ipa' => '/' . fake()->lexify('???') . '/',
            'meaning' => fake()->sentence(),
            'audio_url' => fake()->url(),
            'difficulty' => fake()->randomElement([
                'beginner',
                'intermediate',
                'advanced'
            ]),
        ];
    }
}
