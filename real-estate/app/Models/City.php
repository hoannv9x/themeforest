<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'state',
        'country',
        'latitude',
        'longitude',
    ];

    // Relationships
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
