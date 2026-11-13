<?php

namespace App\Policies;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GymPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Gym $gym): Response
    {
        return $gym->is_active ? Response::allow() : Response::denyAsNotFound();
    }
}
