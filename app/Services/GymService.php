<?php

namespace App\Services;

use App\Models\Gym;
use Illuminate\Pagination\LengthAwarePaginator;

class GymService
{
    /** @return LengthAwarePaginator<int, Gym> */
    public function listActiveGyms(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return Gym::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function details(Gym $gym): Gym
    {
        abort_unless($gym->is_active, 404);

        return $gym->load(['photos', 'openingHours']);
    }
}
