<?php

namespace Database\Factories;

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'membership_plan_id' => MembershipPlan::factory(),
            'plan_name' => fn (array $attributes): string => MembershipPlan::findOrFail($attributes['membership_plan_id'])->name,
            'price_amount' => fn (array $attributes): int => MembershipPlan::findOrFail($attributes['membership_plan_id'])->price_amount,
            'currency' => fn (array $attributes): string => MembershipPlan::findOrFail($attributes['membership_plan_id'])->currency,
            'duration_days' => fn (array $attributes): int => MembershipPlan::findOrFail($attributes['membership_plan_id'])->duration_days,
            'allowed_days' => fn (array $attributes): array => MembershipPlan::findOrFail($attributes['membership_plan_id'])->allowed_days,
            'access_ends_at' => fn (array $attributes): ?string => MembershipPlan::findOrFail($attributes['membership_plan_id'])->access_ends_at,
            'starts_at' => CarbonImmutable::now('UTC'),
            'ends_at' => fn (array $attributes): CarbonImmutable => CarbonImmutable::parse($attributes['starts_at'], 'UTC')->addDays($attributes['duration_days']),
            'cancelled_at' => null,
            'is_demo' => true,
        ];
    }
}
