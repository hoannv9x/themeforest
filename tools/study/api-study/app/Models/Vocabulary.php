<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vocabulary extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['word', 'slug', 'ipa', 'meaning', 'audio_url', 'difficulty'];

    protected $slugSource = 'word';

    public function examples()
    {
        return $this->hasMany(VocabularyExample::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_vocabulary')
            ->withPivot('is_favorite', 'status')
            ->withTimestamps();
    }
}
