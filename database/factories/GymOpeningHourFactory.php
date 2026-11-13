<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\GymOpeningHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymOpeningHour>
 */
class GymOpeningHourFactory extends Factory
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
            'day_of_week' => 1,
            'opens_at' => '07:00',
            'closes_at' => '22:00',
        ];
    }
}
