<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymOpeningHourResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'day_of_week' => $this->day_of_week,
            'opens_at' => $this->opens_at === null ? null : substr($this->opens_at, 0, 5),
            'closes_at' => $this->closes_at === null ? null : substr($this->closes_at, 0, 5),
            'is_closed' => $this->opens_at === null && $this->closes_at === null,
        ];
    }
}
