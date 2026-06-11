<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Carbon\Carbon;

class CourseService
{
    public function getAllCourses()
    {
        return Course::where('status', 'published')->get();
    }

    public function getCourseBySlug($slug, $user = null)
    {
        $course = Course::with(['modules.lessons'])->where('slug', $slug)->firstOrFail();

        if ($user) {
            $completedLessonIds = LessonProgress::where('user_id', $user->id)
                ->pluck('lesson_id')
                ->toArray();

            foreach ($course->modules as $module) {
                foreach ($module->lessons as $lesson) {
                    $lesson->is_completed = in_array($lesson->id, $completedLessonIds);
                }
            }
        }

        return $course;
    }

    public function getLessonBySlug($slug, $user = null)
    {
        $lesson = Lesson::with(['module.course', 'quiz'])->where('slug', $slug)->firstOrFail();

        if ($user) {
            $progress = LessonProgress::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();
            $lesson->is_completed = !!$progress;
        }

        return $lesson;
    }

    public function completeLesson($lessonId, $userId)
    {
        return LessonProgress::updateOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['completed_at' => Carbon::now()]
        );
    }

    public function getUserCourses($userId)
    {
        $courseIds = LessonProgress::where('user_id', $userId)
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->join('modules', 'lessons.module_id', '=', 'modules.id')
            ->pluck('modules.course_id')
            ->unique();

        return Course::whereIn('id', $courseIds)->get();
    }
}
