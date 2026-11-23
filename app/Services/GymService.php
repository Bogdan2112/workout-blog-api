<?php

namespace App\Services;

use App\Models\Gym;
use Illuminate\Pagination\LengthAwarePaginator;

class GymService
{
    /** @return LengthAwarePaginator<int, Gym> */
    public function listActiveGyms(int $perPage = 15, int $page = 1, ?string $city = null): LengthAwarePaginator
    {
        $gyms = Gym::query()->active();

        if ($city !== null) {
            $gyms->inCity($city);
        }

        return $gyms->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends(['city' => $city, 'per_page' => $perPage]);
    }

    public function details(Gym $gym): Gym
    {
        abort_unless($gym->is_active, 404);

        return $gym->load(['photos', 'openingHours']);
    }
}
