<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'city' => $this->city,
            'address' => $this->address,
            'timezone' => $this->timezone,
            'contact_email' => $this->contact_email,
            'phone' => $this->phone,
            'photos' => GymPhotoResource::collection($this->whenLoaded('photos')),
            'opening_hours' => GymOpeningHourResource::collection($this->whenLoaded('openingHours')),
        ];
    }
}
