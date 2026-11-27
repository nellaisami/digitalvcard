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
                && ($this->hasManagementRole($user, $organization)
                    || ($user->role === 'admin' && $this->hasActiveMembership($user, $organization))
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
                && ($this->hasManagementRole($user, $organization)
                    || ($organization->owner_user_id === $user->id
                        && $this->hasActiveRole($user, $organization, 'owner'))));
    }

    public function updateLogo(object $user, Organization $organization): bool
    {
        return $this->canManageOrganizationSettings($user, $organization);
    }

    public function updateTemplate(object $user, Organization $organization): bool
    {
        return $this->canManageOrganizationSettings($user, $organization);
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

    private function hasManagementRole(User $user, Organization $organization): bool
    {
        return $this->hasActiveRole($user, $organization, 'manager');
    }

    private function hasActiveMembership(User $user, Organization $organization): bool
    {
        return $organization->users()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();
    }

    private function canManageOrganizationSettings(object $user, Organization $organization): bool
    {
        return $user instanceof User
            && $organization->status === 'active'
            && (($user->role === 'admin' && $this->hasActiveMembership($user, $organization))
                || ($organization->owner_user_id === $user->id
                    && $this->hasActiveRole($user, $organization, 'owner')));
    }
}
