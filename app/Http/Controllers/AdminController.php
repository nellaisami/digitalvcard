<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUserRequest;
use App\Models\User;
use App\Notifications\AdminEmailVerificationNotification;
use App\Services\OrganizationMembershipManager;
use App\Services\ProfilePhotoManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $admins = User::where('role', 'admin')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admins.index', compact('admins'));
    }

    public function create(OrganizationMembershipManager $memberships): View
    {
        return view('users.form', [
            'user' => new User,
            'mode' => 'admin',
            'organizations' => $memberships->organizationsForUser(new User),
        ]);
    }

    public function store(
        SaveUserRequest $request,
        ProfilePhotoManager $photos,
        OrganizationMembershipManager $memberships,
    ): RedirectResponse {
        $data = $request->validated();
        $organizationAssignments = $data['organization_assignments'] ?? [];
        unset($data['profile_photo'], $data['remove_photo'], $data['organization_assignments']);
        $data['role'] = 'admin';
        $user = DB::transaction(function () use ($data, $organizationAssignments, $memberships): User {
            $user = User::create($data);
            $memberships->sync($user, $organizationAssignments);

            return $user;
        });
        $photos->sync($user, $request->file('profile_photo'), $request->boolean('remove_photo'));
        $user->notify(new AdminEmailVerificationNotification);

        return redirect()->route('admin.index')->with('status', "Admin account created. A verification email has been sent to {$user->email}.");
    }

    public function verifyEmail(User $user, string $hash): RedirectResponse
    {
        abort_unless($user->role === 'admin' && hash_equals(sha1($user->email), $hash), 403);

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->route('login')->with('status', 'Email address verified. You can now sign in.');
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'admin', 404);

        return view('users.show', ['user' => $user, 'mode' => 'admin']);
    }

    public function edit(User $user, OrganizationMembershipManager $memberships): View
    {
        abort_unless($user->role === 'admin', 404);

        return view('users.form', [
            'user' => $user,
            'mode' => 'admin',
            'organizations' => $memberships->organizationsForUser($user),
        ]);
    }

    public function update(
        SaveUserRequest $request,
        User $user,
        ProfilePhotoManager $photos,
        OrganizationMembershipManager $memberships,
    ): RedirectResponse {
        abort_unless($user->role === 'admin', 404);
        $data = $request->validated();
        $organizationAssignments = $data['organization_assignments'] ?? [];
        unset($data['profile_photo'], $data['remove_photo'], $data['organization_assignments']);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        if ($user->email !== $data['email']) {
            $data['email_verified_at'] = null;
        }
        DB::transaction(function () use ($user, $data, $organizationAssignments, $memberships): void {
            $user->update($data);
            $memberships->sync($user, $organizationAssignments);
        });
        $photos->sync($user, $request->file('profile_photo'), $request->boolean('remove_photo'));

        return redirect()->route('admin.index')->with('status', 'Admin account updated.');
    }

    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        abort_unless($user->role === 'admin', 404);
        if ($request->user()->is($user)) {
            return response()->json(['message' => 'You cannot deactivate the account you are using.'], 422);
        }

        $status = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $status]);

        return response()->json([
            'status' => $status,
            'label' => ucfirst($status),
            'message' => "Admin account {$status}.",
        ]);
    }

    public function destroy(Request $request, User $user, ProfilePhotoManager $photos): RedirectResponse
    {
        abort_unless($user->role === 'admin', 404);
        if ($request->user()->is($user)) {
            return back()->withErrors(['admin' => 'You cannot delete the account you are currently using.']);
        }
        if ($user->ownedOrganizations()->exists()) {
            return back()->withErrors(['admin' => 'Delete or transfer this user’s organization ownership before deleting the account.']);
        }

        $photo = $user->profile_photo;
        $user->delete();
        $photos->delete($photo);

        return redirect()->route('admin.index')->with('status', 'Admin account deleted.');
    }
}
