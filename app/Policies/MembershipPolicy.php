<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MembershipPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Membership $membership): Response
    {
        return $user->id === $membership->user_id ? Response::allow() : Response::denyAsNotFound();
    }
}
