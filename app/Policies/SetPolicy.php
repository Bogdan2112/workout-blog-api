<?php

namespace App\Policies;

use App\Models\Set;
use App\Models\User;
use App\Models\Exercise;
use Illuminate\Auth\Access\Response;

class SetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Set $set): bool
    {
        return $user->id === $set->exercise->workout->week->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->workout->week->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Set $set): bool
    {
        return $user->id === $set->exercise->workout->week->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Set $set): bool
    {
        return $user->id === $set->exercise->workout->week->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Set $set): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Set $set): bool
    {
        return false;
    }
}
