<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrganizationMembershipManager
{
    public function organizationsForUser(User $user): Collection
    {
        return Organization::query()
            ->with(['users' => fn ($query) => $query->whereKey($user->exists ? $user->getKey() : 0)])
            ->orderBy('name')
            ->get();
    }

    public function sync(User $user, array $assignments): void
    {
        if ($assignments === []) {
            return;
        }

        $organizations = Organization::query()
            ->whereKey(array_column($assignments, 'id'))
            ->get()
            ->keyBy('id');
        $currentMemberships = DB::table('organization_users')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('organization_id');
        $toAttach = [];
        $toDetach = [];

        foreach ($assignments as $assignment) {
            $organizationId = (int) $assignment['id'];
            $organization = $organizations->get($organizationId) ?? Organization::findOrFail($organizationId);
            $membership = $currentMemberships->get($organizationId);

            if ((int) $organization->owner_user_id === (int) $user->id || $membership?->role === 'owner') {
                continue;
            }

            $role = $assignment['role'] ?? null;
            if ($role === null) {
                if ($membership) {
                    $toDetach[] = $organizationId;
                }

                continue;
            }

            $toAttach[$organizationId] = [
                'role' => $role,
                'status' => $membership?->status ?? 'active',
            ];
        }

        if ($toDetach !== []) {
            $user->organizations()->detach($toDetach);
        }
        if ($toAttach !== []) {
            $user->organizations()->syncWithoutDetaching($toAttach);
        }
    }
}
