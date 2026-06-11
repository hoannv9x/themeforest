<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Quiz\SubmitQuizRequest;
use App\Services\QuizService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    use ApiResponse;

    protected QuizService $quizService;

    public function __construct(QuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    public function show(int $id)
    {
        $quiz = $this->quizService->getQuizById($id);
        return $this->success($quiz);
    }

    public function submit(SubmitQuizRequest $request, int $id)
    {
        $result = $this->quizService->submitQuiz($id, Auth::id(), $request->answers);
        return $this->success($result, 'Quiz submitted successfully');
    }

    public function results()
    {
        $results = $this->quizService->getUserResults(Auth::id());
        return $this->success($results);
    }
}
