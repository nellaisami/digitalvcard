<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUserRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\ProfilePhotoManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function create(): View
    {
        return view('users.form', [
            'user' => new User,
            'plans' => collect(),
            'mode' => 'admin',
        ]);
    }

    public function store(SaveUserRequest $request, ProfilePhotoManager $photos): RedirectResponse
    {
        $data = $request->validated();
        unset($data['profile_photo'], $data['remove_photo'], $data['plan_id']);
        $data['role'] = 'admin';
        $user = User::create($data);
        $photos->sync($user, $request->file('profile_photo'), $request->boolean('remove_photo'));

        return redirect()->route('admin.index')->with('status', 'Admin account created.');
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'admin', 404);
        $user->load('plan');

        return view('users.show', ['user' => $user, 'mode' => 'admin']);
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === 'admin', 404);

        return view('users.form', [
            'user' => $user,
            'plans' => collect(),
            'mode' => 'admin',
        ]);
    }

    public function update(SaveUserRequest $request, User $user, ProfilePhotoManager $photos): RedirectResponse
    {
        abort_unless($user->role === 'admin', 404);
        $data = $request->validated();
        unset($data['profile_photo'], $data['remove_photo'], $data['plan_id']);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        if ($user->email !== $data['email']) {
            $data['email_verified_at'] = null;
        }
        $user->update($data);
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

        $photo = $user->profile_photo;
        $user->delete();
        $photos->delete($photo);

        return redirect()->route('admin.index')->with('status', 'Admin account deleted.');
    }
}
