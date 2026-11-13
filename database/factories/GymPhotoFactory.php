<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\GymPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymPhoto>
 */
class GymPhotoFactory extends Factory
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
            'path' => 'gyms/demo/fitness.jpg',
            'sort_order' => 0,
        ];
    }
}
