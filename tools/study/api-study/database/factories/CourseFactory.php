<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->randomElement([
            'English Foundation: Level 1',
            'English Foundation: Level 2',
            'IELTS Mastery: 7.0+',
            'Business Communication for Tech',
            'Advanced Vocabulary for Writing',
            'English for Travel & Tourism',
            'Grammar Core: Mastering Tenses',
            'Corporate Presentation Skills'
        ]);
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 1000),
            'description' => fake()->sentence(20),
            'thumbnail' => 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?q=80&w=800&auto=format&fit=crop',
            'status' => 'published',
        ];
    }
}
