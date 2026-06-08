<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizResult;
use Carbon\Carbon;

class QuizService
{
    public function getQuizById($id)
    {
        return Quiz::with(['questions.answers' => function ($query) {
            $query->select('id', 'question_id', 'content');
        }])->findOrFail($id);
    }

    public function submitQuiz($quizId, $userId, $answers)
    {
        $quiz = Quiz::with('questions.answers')->findOrFail($quizId);
        
        $userAnswersMap = [];
        foreach ($answers as $ans) {
            $userAnswersMap[$ans['question_id']] = $ans['answer_id'];
        }

        $score = 0;
        $totalPoints = 0;
        $correctCount = 0;
        $resultsDetail = [];

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
            $userAnswerId = $userAnswersMap[$question->id] ?? null;
            $isCorrect = false;

            $correctAnswer = $question->answers->where('is_correct', true)->first();
            if ($correctAnswer && $correctAnswer->id == $userAnswerId) {
                $isCorrect = true;
                $score += $question->points;
                $correctCount++;
            }

            $resultsDetail[] = [
                'question_id' => $question->id,
                'user_answer' => $userAnswerId,
                'is_correct' => $isCorrect
            ];
        }

        $finalScore = $totalPoints > 0 ? round(($score / $totalPoints) * 100) : 0;

        $result = QuizResult::create([
            'user_id' => $userId,
            'quiz_id' => $quiz->id,
            'score' => $finalScore,
            'total_points' => $totalPoints,
            'user_answers' => $resultsDetail,
            'completed_at' => Carbon::now()
        ]);

        return [
            'score' => $finalScore,
            'correct_answers' => $correctCount,
            'total_questions' => $quiz->questions->count(),
            'result_id' => $result->id
        ];
    }

    public function getUserResults($userId)
    {
        return QuizResult::with('quiz')
            ->where('user_id', $userId)
            ->orderBy('completed_at', 'desc')
            ->get();
    }
}
