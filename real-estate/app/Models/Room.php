<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';
    protected $fillable = [
        'property_id',
        'name',
        'slug',
    ];

    public function image()
    {
        return $this->hasOne(Room360Image::class);
    }

    public function hotspots()
    {
        return $this->hasMany(RoomHotspot::class, 'from_room_id');
    }

    public function hotspotsTo()
    {
        return $this->hasMany(RoomHotspot::class, 'to_room_id');
    }
}
