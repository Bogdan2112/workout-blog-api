<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gym>
 */
class GymFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->company().' Fitness',
            'description' => fake()->paragraph(),
            'city' => 'Chișinău',
            'address' => fake()->streetAddress(),
            'timezone' => 'Europe/Chisinau',
            'contact_email' => fake()->safeEmail(),
            'phone' => null,
            'is_active' => true,
        ];
    }
}
