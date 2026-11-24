<?php

namespace App\Services;

use App\Models\Gym;
use App\Models\TrainingSession;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

class TrainingSessionService
{
    /** @return LengthAwarePaginator<int, TrainingSession> */
    public function listForDate(Gym $gym, ?string $date = null, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        abort_unless($gym->is_active, 404);

        $day = $date === null
            ? CarbonImmutable::now($gym->timezone)->startOfDay()
            : CarbonImmutable::createFromFormat('!Y-m-d', $date, $gym->timezone);

        return $gym->trainingSessions()
            ->where('starts_at', '>=', $day->utc())
            ->where('starts_at', '<', $day->addDay()->utc())
            ->orderBy('starts_at')->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends(['date' => $day->toDateString(), 'per_page' => $perPage]);
    }

    public function findForGym(Gym $gym, int $sessionId): TrainingSession
    {
        abort_unless($gym->is_active, 404);

        return $gym->trainingSessions()->findOrFail($sessionId);
    }
}
