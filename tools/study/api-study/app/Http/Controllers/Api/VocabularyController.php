<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Vocabulary\UpdateStatusRequest;
use App\Services\VocabularyService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class VocabularyController extends Controller
{
    use ApiResponse;

    protected $vocabularyService;

    public function __construct(VocabularyService $vocabularyService)
    {
        $this->vocabularyService = $vocabularyService;
    }

    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        $vocabularies = $this->vocabularyService->getVocabularies($request->all(), $user);
        return $this->success($vocabularies);
    }

    public function show(string $slug)
    {
        $user = auth('sanctum')->user();
        $vocabulary = $this->vocabularyService->getVocabularyBySlug($slug, $user);
        return $this->success($vocabulary);
    }

    public function toggleFavorite(int $id)
    {
        $user = Auth::user();
        $isFavorite = $this->vocabularyService->toggleFavorite($id, $user->id);
        return $this->success(['is_favorite' => $isFavorite], 'Favorite status updated');
    }

    public function updateStatus(UpdateStatusRequest $request, int $id)
    {
        $user = Auth::user();
        $status = $this->vocabularyService->updateStatus($id, $user->id, $request->status);
        return $this->success(['status' => $status], 'Learning status updated');
    }
}
