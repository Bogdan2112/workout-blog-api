<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
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
                'Bench Press',
                'Incline Dumbbell Press',
                'Shoulder Press',
                'Lateral Raises',
                'Pull Ups',
                'Lat Pulldown',
                'Barbell Row',
                'Bicep Curls',
                'Tricep Pushdown',
                'Squats',
                'Leg Press',
                'Romanian Deadlift',
                'Leg Curl',
                'Calf Raises',
            ]),
        ];
    }
}
