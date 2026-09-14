<?php

namespace Database\Factories;

use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workout>
 */
class WorkoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'name' => fake()->randomElement([
                'Push Day',
                'Pull Day',
                'Leg Day',
                'Upper Body',
                'Lower Body',
                'Chest & Triceps',
                'Back & Biceps',
                'Shoulders',
            ]),
        ];
    }
}
