<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomHotspotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'to_room_slug' => optional($this->toRoom)->slug,
            'pitch' => $this->pitch !== null ? (float) $this->pitch : null,
            'yaw' => $this->yaw !== null ? (float) $this->yaw : null,
            'text' => $this->text,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
