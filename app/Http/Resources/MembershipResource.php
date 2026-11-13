<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membership_plan_id' => $this->membership_plan_id,
            'plan_name' => $this->plan_name,
            'gym' => new GymResource($this->membershipPlan->gym),
            'price_amount' => $this->price_amount,
            'currency' => $this->currency,
            'duration_days' => $this->duration_days,
            'allowed_days' => $this->allowed_days,
            'access_ends_at' => $this->access_ends_at === null ? null : substr($this->access_ends_at, 0, 5),
            'starts_at' => $this->starts_at->toISOString(),
            'ends_at' => $this->ends_at->toISOString(),
            'status' => $this->statusAt(now()),
            'is_demo' => $this->is_demo,
        ];
    }
}
