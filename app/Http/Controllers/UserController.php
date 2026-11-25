<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\ProfilePhotoManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('plan')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->when($request->filled('plan_id'), fn ($query) => $query->where('plan_id', $request->integer('plan_id')))
            ->when($request->input('verification') === 'verified', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when($request->input('verification') === 'unverified', fn ($query) => $query->whereNull('email_verified_at'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'plans' => Plan::orderBy('name')->get(),
            'roles' => ['superadmin', 'admin', 'manager', 'editor', 'staff'],
        ]);
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => new User,
            'plans' => Plan::where('status', 'active')->orderBy('name')->get(),
            'mode' => 'user',
        ]);
    }

    public function store(SaveUserRequest $request, ProfilePhotoManager $photos): RedirectResponse
    {
        $data = $request->validated();
        unset($data['profile_photo'], $data['remove_photo']);
        $data['role'] = 'staff';
        $user = User::create($data);
        $photos->sync($user, $request->file('profile_photo'), $request->boolean('remove_photo'));

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $user->load('plan');

        return view('users.show', ['user' => $user, 'mode' => 'user']);
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'user' => $user,
            'plans' => Plan::where('status', 'active')
                ->when($user->plan_id, fn ($query) => $query->orWhereKey($user->plan_id))
                ->orderBy('name')
                ->get(),
            'mode' => 'user',
        ]);
    }

    public function update(SaveUserRequest $request, User $user, ProfilePhotoManager $photos): RedirectResponse
    {
        $data = $request->validated();
        unset($data['profile_photo'], $data['remove_photo']);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        if ($user->email !== $data['email']) {
            $data['email_verified_at'] = null;
        }
        $user->update($data);
        $photos->sync($user, $request->file('profile_photo'), $request->boolean('remove_photo'));

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function editRole(User $user): View
    {
        return view('users.role-form', ['user' => $user]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $role = $request->validated('role');
        if ($user->role === 'superadmin' && $role !== 'superadmin' && User::where('role', 'superadmin')->count() <= 1) {
            return back()->withErrors(['role' => 'At least one Super Admin account must remain.']);
        }

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

        $photo = $user->profile_photo;
        $user->delete();
        $photos->delete($photo);

        return redirect()->route('users.index')->with('status', 'User deleted successfully.');
    }
}
