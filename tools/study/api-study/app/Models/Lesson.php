<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'module_id',
        'tenant_id',
        'title',
        'slug',
        'content_type',
        'video_url',
        'content',
        'sort_order'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
