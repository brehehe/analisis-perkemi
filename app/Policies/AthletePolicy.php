<?php

namespace App\Policies;

use App\Models\Athlete;
use App\Models\User;

class AthletePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('athletes.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Athlete $athlete): bool
    {
        return $user->can('athletes.view')
            || ($user->hasRole('athlete') && $athlete->user_id === $user->getKey());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('athletes.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Athlete $athlete): bool
    {
        return $user->can('athletes.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Athlete $athlete): bool
    {
        return $user->can('athletes.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Athlete $athlete): bool
    {
        return $user->can('athletes.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Athlete $athlete): bool
    {
        return false;
    }
}
