<?php

namespace App\Policies;

use App\Models\MatchRecord;
use App\Models\User;

class MatchRecordPolicy
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
        return $user->can('matches.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MatchRecord $matchRecord): bool
    {
        return $user->can('matches.view')
            || ($user->hasRole('athlete') && $matchRecord->athletes()
                ->where('user_id', $user->getKey())
                ->exists());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('matches.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MatchRecord $matchRecord): bool
    {
        return $user->can('matches.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MatchRecord $matchRecord): bool
    {
        return $user->can('matches.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MatchRecord $matchRecord): bool
    {
        return $user->can('matches.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MatchRecord $matchRecord): bool
    {
        return false;
    }
}
