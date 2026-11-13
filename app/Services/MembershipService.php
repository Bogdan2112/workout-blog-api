<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class MembershipService
{
    /** @return LengthAwarePaginator<int, Membership> */
    public function listForUser(User $user, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $user->memberships()->with('membershipPlan.gym')
            ->orderByDesc('starts_at')->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findForUser(User $user, int $membershipId): Membership
    {
        return $user->memberships()->with('membershipPlan.gym')->findOrFail($membershipId);
    }
}
