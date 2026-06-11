<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['tenant_id', 'title', 'slug', 'description', 'thumbnail', 'status'];

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('sort_order');
    }
}
