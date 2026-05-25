<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'boarding_house_id',
    'name',
    'status',
    'rent_amount',
    'deposit_amount',
    'electricity_rate',
    'water_rate',
    'wifi_fee',
    'trash_fee',
    'parking_fee',
    'initial_electricity_reading',
    'initial_water_reading',
])]
class Room extends Model
{
    use HasFactory;

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function roomTenants(): HasMany
    {
        return $this->hasMany(RoomTenant::class);
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}

