<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['landlord_id', 'name', 'address', 'note'])]
class BoardingHouse extends Model
{
    use HasFactory;

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}

