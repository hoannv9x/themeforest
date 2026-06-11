<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VocabularyExample extends Model
{
    use HasFactory;
    protected $fillable = ['vocabulary_id', 'example_sentence', 'translation'];

    public function vocabulary()
    {
        return $this->belongsTo(Vocabulary::class);
    }
}
