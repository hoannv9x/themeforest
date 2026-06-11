<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);
        $type = fake()->randomElement(['video', 'text']);
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 1000),
            'content_type' => $type,
            'video_url' => $type === 'video' ? 'https://www.youtube.com/embed/dQw4w9WgXcQ' : null,
            'content' => fake()->paragraphs(3, true),
            'sort_order' => 1,
        ];
    }
}
