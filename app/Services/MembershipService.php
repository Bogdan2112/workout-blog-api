<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    public function createDemoMembership(User $user, MembershipPlan $plan, CarbonImmutable $startsAt): Membership
    {
        abort_unless(app()->environment(['local', 'testing']), 403);
        abort_unless($plan->is_active && $plan->gym->is_active, 404);

        return DB::transaction(function () use ($user, $plan, $startsAt): Membership {
            $membership = new Membership([
                'plan_name' => $plan->name,
                'price_amount' => $plan->price_amount,
                'currency' => $plan->currency,
                'duration_days' => $plan->duration_days,
                'allowed_days' => $plan->allowed_days,
                'access_ends_at' => $plan->access_ends_at,
                'starts_at' => $startsAt->utc(),
                'ends_at' => $startsAt->utc()->addDays($plan->duration_days),
                'is_demo' => true,
            ]);
            $membership->user()->associate($user);
            $membership->membershipPlan()->associate($plan);
            $membership->save();

            return $membership;
        });
    }
}
