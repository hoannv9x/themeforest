<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
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
            'city_id' => $this->city_id,
            'city' => new CityResource($this->whenLoaded('city')),
            'name' => $this->name,
            'slug' => $this->slug,
            'properties_count' => $this->whenCounted('properties'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
