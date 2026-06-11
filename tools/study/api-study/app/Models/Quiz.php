<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $fillable = ['lesson_id', 'title', 'description'];

    public function lesson()
    {
        return $this->hasOne(Lesson::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function results()
    {
        return $this->hasMany(QuizResult::class);
    }
}
