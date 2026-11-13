<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gym_id' => Gym::factory(),
            'name' => 'Full Time — 1 lună',
            'description' => 'Acces în toate zilele, în programul sălii.',
            'image_path' => null,
            'price_amount' => 60000,
            'currency' => 'MDL',
            'duration_days' => 30,
            'allowed_days' => [1, 2, 3, 4, 5, 6, 7],
            'access_ends_at' => null,
            'is_active' => true,
        ];
    }

    public function partTime(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Part Time — 1 lună',
            'allowed_days' => [1, 2, 3, 4, 5],
            'access_ends_at' => '17:00',
        ]);
    }

    public function weekend(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Weekend — 1 lună',
            'allowed_days' => [6, 7],
            'access_ends_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }
}
