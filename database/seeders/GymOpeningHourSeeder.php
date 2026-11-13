<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Services\GymOpeningHourService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

class GymOpeningHourSeeder extends Seeder
{
    public function run(GymOpeningHourService $service): void
    {
        $gyms = Gym::query()->whereHas('owner', fn (Builder $query): Builder => $query->where('name', 'Proprietar Fitline Demo'))->get();

        foreach ($gyms as $gym) {
            $existingHours = $gym->openingHours()->get()->keyBy('day_of_week');
            $schedule = [];

            foreach (range(1, 7) as $day) {
                $existing = $existingHours->get($day);
                $schedule[] = [
                    'day_of_week' => $day,
                    'opens_at' => $existing !== null ? ($existing->opens_at === null ? null : substr($existing->opens_at, 0, 5)) : ($day <= 5 ? '07:00' : '09:00'),
                    'closes_at' => $existing !== null ? ($existing->closes_at === null ? null : substr($existing->closes_at, 0, 5)) : ($day <= 5 ? '22:00' : ($day === 6 ? '20:00' : '18:00')),
                ];
            }

            $service->setWeeklySchedule($gym, $schedule);
        }
    }
}
