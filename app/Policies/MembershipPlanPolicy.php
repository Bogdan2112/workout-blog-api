<?php

namespace App\Policies;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MembershipPlanPolicy
{
    public function view(User $user, MembershipPlan $membershipPlan): Response
    {
        return $membershipPlan->is_active && $membershipPlan->gym->is_active
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
