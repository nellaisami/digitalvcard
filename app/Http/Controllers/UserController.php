<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use App\Notifications\OrganizationOwnerEmailVerificationNotification;
use App\Services\OrganizationMembershipManager;
use App\Services\ProfilePhotoManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->when($request->input('verification') === 'verified', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when($request->input('verification') === 'unverified', fn ($query) => $query->whereNull('email_verified_at'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => ['admin', 'manager', 'editor', 'staff'],
        ]);
    }

    public function create(OrganizationMembershipManager $memberships): View
    {
        $user = new User;

        return view('users.form', [
            'user' => $user,
            'organizations' => $memberships->organizationsForUser($user),
            'mode' => 'user',
        ]);
    }

    public function store(
        SaveUserRequest $request,
        ProfilePhotoManager $photos,
        OrganizationMembershipManager $memberships,
    ): RedirectResponse {
        $data = $request->validated();
        unset($data['profile_photo'], $data['remove_photo']);
        $organizationAssignments = $data['organization_assignments'] ?? [];
        unset($data['organization_assignments']);
        $data['role'] = 'staff';
        $user = DB::transaction(function () use ($data, $organizationAssignments, $memberships): User {
            $user = User::create($data);
            $memberships->sync($user, $organizationAssignments);

            return $user;
        });
        $photos->sync($user, $request->file('profile_photo'), $request->boolean('remove_photo'));

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user): View
    {
        return view('users.show', ['user' => $user, 'mode' => 'user']);
    }

    public function edit(User $user, OrganizationMembershipManager $memberships): View
    {
        return view('users.form', [
            'user' => $user,
            'organizations' => $user->role === 'staff'
                ? $memberships->organizationsForUser($user)
                : collect(),
            'mode' => 'user',
        ]);
    }

    public function update(
        SaveUserRequest $request,
        User $user,
        ProfilePhotoManager $photos,
        OrganizationMembershipManager $memberships,
    ): RedirectResponse {
        $data = $request->validated();
        unset($data['profile_photo'], $data['remove_photo']);
        $syncOrganizationAssignments = array_key_exists('organization_assignments', $data);
        $organizationAssignments = $data['organization_assignments'] ?? [];
        unset($data['organization_assignments']);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        if ($user->email !== $data['email']) {
            $data['email_verified_at'] = null;
        }
        $emailChanged = $user->email !== $data['email'];
        $ownedOrganizations = $emailChanged ? $user->ownedOrganizations()->get() : collect();
        DB::transaction(function () use ($data, $user, $syncOrganizationAssignments, $organizationAssignments, $memberships): void {
            $user->update($data);
            if ($syncOrganizationAssignments) {
                $memberships->sync($user, $organizationAssignments);
            }
        });
        $photos->sync($user, $request->file('profile_photo'), $request->boolean('remove_photo'));
        foreach ($ownedOrganizations as $organization) {
            $user->notify(new OrganizationOwnerEmailVerificationNotification($organization));
        }

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function editRole(User $user): View
    {
        return view('users.role-form', ['user' => $user]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $role = $request->validated('role');
        $user->update(['role' => $role]);

        return redirect()->route('users.index')->with('status', 'User role updated.');
    }

    public function toggleVerification(User $user): JsonResponse
    {
        $verified = $user->email_verified_at === null;
        $user->forceFill(['email_verified_at' => $verified ? now() : null])->save();

        return response()->json([
            'verified' => $verified,
            'label' => $verified ? 'Verified' : 'Not verified',
            'message' => $verified ? 'Email marked as verified.' : 'Email verification removed.',
        ]);
    }

    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json(['message' => 'You cannot deactivate the account you are using.'], 422);
        }

        $status = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $status]);

        return response()->json([
            'status' => $status,
            'label' => ucfirst($status),
            'message' => "User account {$status}.",
        ]);
    }

    public function destroy(Request $request, User $user, ProfilePhotoManager $photos): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete the account you are currently using.']);
        }
        if ($user->ownedOrganizations()->exists()) {
            return back()->withErrors(['user' => 'Delete or transfer this user’s organization ownership before deleting the account.']);
        }

        $photo = $user->profile_photo;
        $user->delete();
        $photos->delete($photo);

        return redirect()->route('users.index')->with('status', 'User deleted successfully.');
    }
}
