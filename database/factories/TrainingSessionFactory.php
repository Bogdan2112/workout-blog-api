<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\TrainingSession;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TrainingSession> */
class TrainingSessionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $startsAt = CarbonImmutable::now('UTC')->addDay()->setTime(16, 0);

        return [
            'gym_id' => Gym::factory(),
            'name' => 'Pilates',
            'description' => 'Ședință de grup pentru mobilitate și control muscular.',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addHour(),
            'capacity' => 15,
            'cancelled_at' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => ['cancelled_at' => CarbonImmutable::now('UTC')]);
    }
}
