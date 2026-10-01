<?php

namespace App\Policies;

use App\Enums\SystemPermission;
use App\Models\User;
use App\Support\Impersonation;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(SystemPermission::ViewUsers->value);
    }

    public function create(User $user): bool
    {
        return $user->can(SystemPermission::CreateUsers->value);
    }

    /**
     * You can only edit users who are not more powerful than you - otherwise an admin
     * could reset a more privileged account's password and take it over.
     */
    public function update(User $user, User $model): bool
    {
        return $user->can(SystemPermission::UpdateUsers->value)
            && $user->outranks($model);
    }

    /**
     * Sign in as another user. Only users you outrank - otherwise impersonation would hand you
     * permissions you don't have - and never while already signed in as someone else.
     */
    public function impersonate(User $user, User $model): bool
    {
        return $user->can(SystemPermission::ImpersonateUsers->value)
            && $user->isNot($model)
            && $user->outranks($model)
            && ! Impersonation::active();
    }

    /**
     * Your own account is deleted from the settings page instead.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->can(SystemPermission::DeleteUsers->value)
            && $user->isNot($model)
            && $user->outranks($model);
    }
}
