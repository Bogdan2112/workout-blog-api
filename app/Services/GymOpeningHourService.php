<?php

namespace App\Services;

use App\Models\Gym;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GymOpeningHourService
{
    /**
     * @param  array<int, array{day_of_week: int, opens_at: ?string, closes_at: ?string}>  $hours
     */
    public function setWeeklySchedule(Gym $gym, array $hours): void
    {
        $validated = Validator::make(['hours' => $hours], [
            'hours' => ['required', 'array', 'size:7'],
            'hours.*' => ['required', 'array:day_of_week,opens_at,closes_at'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:1,7', 'distinct'],
            'hours.*.opens_at' => ['present', 'nullable', 'required_with:hours.*.closes_at', 'date_format:H:i'],
            'hours.*.closes_at' => ['present', 'nullable', 'required_with:hours.*.opens_at', 'date_format:H:i', 'after:hours.*.opens_at'],
        ])->validate();

        DB::transaction(function () use ($gym, $validated): void {
            foreach ($validated['hours'] as $day) {
                $gym->openingHours()->updateOrCreate(
                    ['day_of_week' => $day['day_of_week']],
                    ['opens_at' => $day['opens_at'], 'closes_at' => $day['closes_at']],
                );
            }
        });
    }
}
