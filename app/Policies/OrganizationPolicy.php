<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(object $user): bool
    {
        return $user->role === 'superadmin';
    }

    public function view(object $user, Organization $organization): bool
    {
        return $user->role === 'superadmin'
            || ($user instanceof User
                && $organization->status === 'active'
                && ($this->hasActiveRole($user, $organization, 'manager')
                    || ($organization->owner_user_id === $user->id
                        && $this->hasActiveRole($user, $organization, 'owner'))));
    }

    public function create(object $user): bool
    {
        return $user->role === 'superadmin';
    }

    public function update(object $user, Organization $organization): bool
    {
        return $user->role === 'superadmin';
    }

    public function manageUsers(object $user, Organization $organization): bool
    {
        return $user->role === 'superadmin'
            || ($user instanceof User
                && $organization->status === 'active'
                && ($this->hasActiveRole($user, $organization, 'manager')
                    || ($organization->owner_user_id === $user->id
                        && $this->hasActiveRole($user, $organization, 'owner'))));
    }

    public function delete(object $user, Organization $organization): bool
    {
        return $user->role === 'superadmin';
    }

    private function hasActiveRole(User $user, Organization $organization, string $role): bool
    {
        return $organization->users()
            ->where('users.id', $user->id)
            ->wherePivot('role', $role)
            ->wherePivot('status', 'active')
            ->exists();
    }
}
