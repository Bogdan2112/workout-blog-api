<?php

namespace App\Services;

use App\Models\Gym;
use App\Models\MembershipPlan;
use Illuminate\Pagination\LengthAwarePaginator;

class MembershipPlanService
{
    /** @return LengthAwarePaginator<int, MembershipPlan> */
    public function listActivePlans(Gym $gym, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        abort_unless($gym->is_active, 404);

        return $gym->membershipPlans()->where('is_active', true)
            ->orderBy('duration_days')->orderBy('name')->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findActivePlan(Gym $gym, int $planId): MembershipPlan
    {
        abort_unless($gym->is_active, 404);

        return $gym->membershipPlans()->where('is_active', true)->findOrFail($planId);
    }
}
