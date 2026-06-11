<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug()
    {
        static::creating(function ($model) {
            $sourceField = $model->slugSource ?? 'title';
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->$sourceField);
            }
        });
    }

    public static function generateUniqueSlug(string $sourceField)
    {
        $slug = Str::slug($sourceField);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
