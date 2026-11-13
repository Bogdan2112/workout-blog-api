<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MembershipPlanResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'gym_id' => $this->gym_id,
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->image_path === null ? null : Storage::disk('public')->url($this->image_path),
            'price_amount' => $this->price_amount,
            'currency' => $this->currency,
            'duration_days' => $this->duration_days,
            'allowed_days' => $this->allowed_days,
            'access_ends_at' => $this->access_ends_at === null ? null : substr($this->access_ends_at, 0, 5),
        ];
    }
}
