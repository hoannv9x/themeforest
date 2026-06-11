<?php

namespace App\Services;

use App\Models\Vocabulary;
use Illuminate\Support\Facades\Auth;

class VocabularyService
{
    public function getVocabularies($filters = [], $user = null)
    {
        $query = Vocabulary::with(['examples']);

        if (!empty($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('word', 'like', "%$search%")
                  ->orWhere('meaning', 'like', "%$search%");
            });
        }

        $vocabularies = $query->paginate(20);

        if ($user) {
            $vocabularies->getCollection()->transform(function ($vocab) use ($user) {
                $userVocab = $vocab->users()->where('user_id', $user->id)->first();
                $vocab->is_favorite = $userVocab ? (bool)$userVocab->pivot->is_favorite : false;
                $vocab->status = $userVocab ? $userVocab->pivot->status : 'new';
                return $vocab;
            });
        }

        return $vocabularies;
    }

    public function getVocabularyBySlug($slug, $user = null)
    {
        $vocabulary = Vocabulary::with('examples')->where('slug', $slug)->firstOrFail();

        if ($user) {
            $userVocab = $vocabulary->users()->where('user_id', $user->id)->first();
            $vocabulary->is_favorite = $userVocab ? (bool)$userVocab->pivot->is_favorite : false;
            $vocabulary->status = $userVocab ? $userVocab->pivot->status : 'new';
        }

        return $vocabulary;
    }

    public function toggleFavorite($vocabularyId, $userId)
    {
        $vocabulary = Vocabulary::findOrFail($vocabularyId);
        $userVocab = $vocabulary->users()->where('user_id', $userId)->first();

        if ($userVocab) {
            $newStatus = !$userVocab->pivot->is_favorite;
            $vocabulary->users()->updateExistingPivot($userId, ['is_favorite' => $newStatus]);
        } else {
            $newStatus = true;
            $vocabulary->users()->attach($userId, ['is_favorite' => true]);
        }

        return $newStatus;
    }

    public function updateStatus($vocabularyId, $userId, $status)
    {
        $vocabulary = Vocabulary::findOrFail($vocabularyId);
        $userVocab = $vocabulary->users()->where('user_id', $userId)->first();

        if ($userVocab) {
            $vocabulary->users()->updateExistingPivot($userId, ['status' => $status]);
        } else {
            $vocabulary->users()->attach($userId, ['status' => $status]);
        }

        return $status;
    }
}
