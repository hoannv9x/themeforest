<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CourseService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    use ApiResponse;

    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index()
    {
        $courses = $this->courseService->getAllCourses();
        return $this->success($courses);
    }

    public function show(string $slug)
    {
        $user = auth('sanctum')->user();
        $course = $this->courseService->getCourseBySlug($slug, $user);
        return $this->success($course);
    }

    public function lesson(string $slug)
    {
        $user = auth('sanctum')->user();
        $lesson = $this->courseService->getLessonBySlug($slug, $user);
        return $this->success($lesson);
    }

    public function completeLesson(int $id)
    {
        $user = Auth::user();
        $progress = $this->courseService->completeLesson($id, $user->id);
        return $this->success($progress, 'Lesson marked as completed');
    }

    public function myCourses()
    {
        $user = Auth::user();
        $courses = $this->courseService->getUserCourses($user->id);
        return $this->success($courses);
    }
}
