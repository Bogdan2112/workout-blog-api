<?php

namespace Database\Factories;

use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;
use HasFactory;
/**
 * @extends Factory<Week>
 */
class WeekFactory extends Factory
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
                'Push Week',
                'Pull Week',
                'Leg Week',
                'Strength Week',
                'Hypertrophy Week',
            ]),
        ];
    }
}
