<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Sử dụng Factory để tạo thêm các khóa học ngẫu nhiên
        Course::factory()
            ->count(5)
            ->create()
            ->each(function ($course) {
                // Mỗi khóa học có 3-5 modules
                Module::factory()
                    ->count(fake()->numberBetween(3, 5))
                    ->create(['course_id' => $course->id])
                    ->each(function ($module) {
                        // Mỗi module có 3-6 lessons
                        Lesson::factory()
                            ->count(fake()->numberBetween(3, 6))
                            ->create(['module_id' => $module->id])
                            ->each(function ($lesson) {
                                // Mỗi lesson có 50% cơ hội có 1 quiz
                                if (fake()->boolean(50)) {
                                    $quiz = Quiz::factory()->create(['lesson_id' => $lesson->id]);

                                    // Mỗi quiz có 3-5 câu hỏi
                                    Question::factory()
                                        ->count(fake()->numberBetween(3, 5))
                                        ->create(['quiz_id' => $quiz->id])
                                        ->each(function ($question) {
                                            // Tạo đáp án cho câu hỏi
                                            if ($question->type === 'true_false') {
                                                Answer::create(['question_id' => $question->id, 'content' => 'True', 'is_correct' => fake()->boolean()]);
                                                Answer::create(['question_id' => $question->id, 'content' => 'False', 'is_correct' => !Answer::where('question_id', $question->id)->first()->is_correct]);
                                            } else {
                                                $correctIndex = fake()->numberBetween(0, 3);
                                                for ($i = 0; $i < 4; $i++) {
                                                    Answer::create([
                                                        'question_id' => $question->id,
                                                        'content' => fake()->words(3, true),
                                                        'is_correct' => $i === $correctIndex
                                                    ]);
                                                }
                                            }
                                        });
                                }
                            });
                    });
            });
    }
}
