<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveOrganizationRequest;
use App\Http\Requests\UpdateOrganizationOwnerPasswordRequest;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Models\VcardTemplate;
use App\Notifications\OrganizationMemberEmailVerificationNotification;
use App\Notifications\OrganizationOwnerEmailVerificationNotification;
use App\Services\OrganizationLogoManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Organization::class);
        $organizations = Organization::query()
            ->with(['plan', 'owner'])
            ->withCount('users')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('owner', fn ($ownerQuery) => $ownerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($request->input('plan_id') === 'none', fn ($query) => $query->whereNull('plan_id'))
            ->when($request->filled('plan_id') && $request->input('plan_id') !== 'none', fn ($query) => $query->where('plan_id', $request->integer('plan_id')))
            ->when($request->input('verification') === 'verified', fn ($query) => $query->whereHas('owner', fn ($owner) => $owner->whereNotNull('email_verified_at')))
            ->when($request->input('verification') === 'unverified', fn ($query) => $query->where(function ($query): void {
                $query->whereDoesntHave('owner')
                    ->orWhereHas('owner', fn ($owner) => $owner->whereNull('email_verified_at'));
            }))
            ->when(in_array($request->input('status'), ['active', 'inactive'], true), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('organizations.index', [
            'organizations' => $organizations,
            'plans' => Plan::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Organization::class);

        return view('organizations.form', [
            'organization' => new Organization(['status' => 'active']),
            'owner' => null,
            'ownerFirstName' => '',
            'ownerLastName' => '',
            'plans' => Plan::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(SaveOrganizationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $ownerName = trim($data['owner_first_name'].' '.$data['owner_last_name']);
        $owner = null;
        $organization = DB::transaction(function () use ($data, $ownerName, &$owner): Organization {
            $owner = User::create([
                'name' => $ownerName,
                'email' => $data['owner_email'],
                'password' => $data['password'],
                'contact_no' => $data['contact_no'] ?? null,
                'role' => 'admin',
                'status' => 'active',
            ]);

            $organization = Organization::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'owner_user_id' => $owner->id,
                'plan_id' => $data['plan_id'] ?? null,
                'status' => $data['status'],
            ]);
            $organization->users()->attach($owner, ['role' => 'owner', 'status' => 'active']);

            return $organization;
        });

        $owner->notify(new OrganizationOwnerEmailVerificationNotification($organization));

        return redirect()->route('organizations.index')->with('status', "Organization created. A verification email has been sent to {$owner->email}.");
    }

    public function show(Organization $organization): View
    {
        Gate::authorize('view', $organization);
        $organization->load(['owner', 'plan', 'users', 'vcardTemplate']);
        $vcards = $organization->vcards()->with('user')->latest()->get();
        $canManageUsers = Gate::allows('manageUsers', $organization);
        $canChangeLogo = Gate::allows('updateLogo', $organization);
        $canChangeTemplate = Gate::allows('updateTemplate', $organization);
        $templates = VcardTemplate::query()
            ->where('status', 'active')
            ->when($organization->vcard_template_id, fn ($query) => $query->orWhereKey($organization->vcard_template_id))
            ->orderBy('name')
            ->get();

        return view('organizations.show', compact('organization', 'vcards', 'canManageUsers', 'canChangeLogo', 'canChangeTemplate', 'templates'));
    }

    public function updateLogo(Request $request, Organization $organization, OrganizationLogoManager $logos): RedirectResponse
    {
        Gate::authorize('updateLogo', $organization);
        $data = $request->validate([
            'logo' => ['nullable', 'required_without:remove_logo', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
        ]);
        $logos->sync($organization, $request->file('logo'), (bool) ($data['remove_logo'] ?? false));

        return redirect()->route('organizations.show', $organization)->with('status', 'Organization logo updated.');
    }

    public function updateTemplate(Request $request, Organization $organization): RedirectResponse
    {
        Gate::authorize('updateTemplate', $organization);
        $data = $request->validate([
            'vcard_template_id' => [
                'nullable',
                'integer',
                Rule::exists(VcardTemplate::class, 'id')->where(function ($query) use ($organization): void {
                    $query->where('status', 'active');
                    if ($organization->vcard_template_id) {
                        $query->orWhere('id', $organization->vcard_template_id);
                    }
                }),
            ],
        ]);
        $organization->update(['vcard_template_id' => $data['vcard_template_id'] ?? null]);

        return redirect()->route('organizations.show', $organization)->with('status', 'Organization vCard template updated.');
    }

    public function createUser(Organization $organization): View
    {
        Gate::authorize('manageUsers', $organization);

        return view('organizations.users.create', compact('organization'));
    }

    public function storeUser(Request $request, Organization $organization): RedirectResponse
    {
        Gate::authorize('manageUsers', $organization);

        $accountType = $request->input('account_type');
        $rules = [
            'account_type' => ['required', Rule::in(['create', 'existing'])],
            'organization_role' => ['required', Rule::in(['member', 'manager'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];

        if ($accountType === 'create') {
            $rules += [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique(User::class, 'email'),
                    Rule::unique(SuperAdmin::class, 'email'),
                ],
                'contact_no' => ['nullable', 'string', 'max:30'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ];
        } else {
            $rules['email'] = ['required', 'email', 'max:255', Rule::exists('users', 'email')];
        }

        $data = $request->validate($rules);
        $isNewAccount = $data['account_type'] === 'create';

        if ($isNewAccount) {
            $member = DB::transaction(function () use ($data, $organization): User {
                $member = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'contact_no' => $data['contact_no'] ?? null,
                    'password' => $data['password'],
                    'role' => 'staff',
                    'status' => 'active',
                ]);
                $organization->users()->attach($member, [
                    'role' => $data['organization_role'],
                    'status' => $data['status'],
                ]);

                return $member;
            });

            $member->notify(new OrganizationMemberEmailVerificationNotification($organization));
        } else {
            $member = User::where('email', $data['email'])->firstOrFail();
            if ($member->role !== 'staff' && ! ($member->role === 'admin' && $request->user()->role === 'superadmin')) {
                throw ValidationException::withMessages([
                    'email' => 'Only standard users can be added by organization managers. A Super Admin can also assign platform Admin accounts.',
                ]);
            }
            if ($organization->users()->whereKey($member->id)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'This user is already associated with the organization.',
                ]);
            }

            $organization->users()->attach($member, [
                'role' => $data['organization_role'],
                'status' => $data['status'],
            ]);
        }

        return redirect()->route('organizations.show', $organization)
            ->with('status', $isNewAccount
                ? "Organization user created. A verification email has been sent to {$member->email}."
                : "{$member->name} added to the organization.");
    }

    public function editUser(Organization $organization, User $member): View
    {
        Gate::authorize('manageUsers', $organization);
        $member = $this->ensureManageableMember($organization, $member);

        return view('organizations.users.edit', compact('organization', 'member'));
    }

    public function updateUser(Request $request, Organization $organization, User $member): RedirectResponse
    {
        Gate::authorize('manageUsers', $organization);
        $this->ensureManageableMember($organization, $member);
        $data = $request->validate([
            'organization_role' => ['required', Rule::in(['member', 'manager'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $organization->users()->updateExistingPivot($member->id, [
            'role' => $data['organization_role'],
            'status' => $data['status'],
        ]);

        return redirect()->route('organizations.show', $organization)->with('status', 'Organization user updated.');
    }

    public function toggleUserStatus(Organization $organization, User $member): JsonResponse
    {
        Gate::authorize('manageUsers', $organization);
        $this->ensureManageableMember($organization, $member);

        $status = DB::transaction(function () use ($organization, $member): string {
            $membership = DB::table('organization_users')
                ->where('organization_id', $organization->id)
                ->where('user_id', $member->id)
                ->lockForUpdate()
                ->first();
            abort_unless($membership, 404);
            $nextStatus = $membership->status === 'active' ? 'inactive' : 'active';
            DB::table('organization_users')
                ->where('organization_id', $organization->id)
                ->where('user_id', $member->id)
                ->update(['status' => $nextStatus, 'updated_at' => now()]);

            return $nextStatus;
        });

        return response()->json([
            'status' => $status,
            'label' => ucfirst($status),
            'message' => "{$member->name}'s organization status is now {$status}.",
        ]);
    }

    public function destroyUser(Organization $organization, User $member): RedirectResponse
    {
        Gate::authorize('manageUsers', $organization);
        $this->ensureManageableMember($organization, $member);
        $organization->users()->detach($member->id);

        return redirect()->route('organizations.show', $organization)
            ->with('status', "{$member->name} was removed from the organization. Their account was retained.");
    }

    public function verifyMemberEmail(Organization $organization, User $member, string $hash): RedirectResponse
    {
        abort_unless(hash_equals(sha1($member->email), $hash), 403);
        if (! $member->email_verified_at) {
            $member->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->route('login')->with('status', 'Organization user email verified. You can now sign in.');
    }

    public function mine(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $organization = $user->ownedOrganizations()->first();
        if (! $organization) {
            $organization = $user->organizations()
                ->where('organization_users.role', 'manager')
                ->wherePivot('status', 'active')
                ->where('organizations.status', 'active')
                ->first();
        }
        if (! $organization && $user->role === 'admin') {
            $organization = $user->organizations()
                ->wherePivot('status', 'active')
                ->where('organizations.status', 'active')
                ->orderBy('organizations.name')
                ->first();
        }
        abort_unless($organization, 404);

        return $this->show($organization);
    }

    public function edit(Organization $organization): View
    {
        Gate::authorize('update', $organization);
        $organization->load('owner');
        [$firstName, $lastName] = $this->splitName($organization->owner?->name);

        return view('organizations.form', [
            'organization' => $organization,
            'owner' => $organization->owner,
            'ownerFirstName' => $firstName,
            'ownerLastName' => $lastName,
            'plans' => Plan::query()
                ->where('status', 'active')
                ->when($organization->plan_id, fn ($query) => $query->orWhereKey($organization->plan_id))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(SaveOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $data = $request->validated();
        $owner = $organization->owner;
        $ownerEmail = $data['owner_email'];
        $emailChanged = ! $owner || $owner->email !== $ownerEmail;
        $ownerName = trim($data['owner_first_name'].' '.$data['owner_last_name']);

        DB::transaction(function () use ($data, $organization, &$owner, $ownerEmail, $ownerName, $emailChanged): void {
            if (! $owner) {
                $owner = User::create([
                    'name' => $ownerName,
                    'email' => $ownerEmail,
                    'password' => $data['password'],
                    'contact_no' => $data['contact_no'] ?? null,
                    'role' => 'admin',
                    'status' => 'active',
                ]);
            } else {
                $owner->forceFill([
                    'name' => $ownerName,
                    'email' => $ownerEmail,
                    'contact_no' => $data['contact_no'] ?? null,
                    'email_verified_at' => $emailChanged ? null : $owner->email_verified_at,
                ])->save();
            }

            $organization->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'owner_user_id' => $owner->id,
                'plan_id' => $data['plan_id'] ?? null,
                'status' => $data['status'],
            ]);
            $organization->users()->syncWithoutDetaching([
                $owner->id => ['role' => 'owner', 'status' => 'active'],
            ]);
        });

        if ($emailChanged) {
            $owner->notify(new OrganizationOwnerEmailVerificationNotification($organization));
        }

        return redirect()->route('organizations.show', $organization)->with('status', 'Organization updated successfully.');
    }

    public function updateOwnerPassword(
        UpdateOrganizationOwnerPasswordRequest $request,
        Organization $organization,
    ): RedirectResponse {
        Gate::authorize('update', $organization);
        $owner = $organization->owner;
        abort_unless($owner, 404);
        $owner->update(['password' => $request->validated('password')]);

        return redirect()->route('organizations.show', $organization)->with('status', 'Organization owner password updated.');
    }

    public function resendVerification(Organization $organization): RedirectResponse
    {
        Gate::authorize('update', $organization);
        $owner = $organization->owner;
        abort_unless($owner, 404);
        if ($owner->email_verified_at) {
            return back()->with('error', 'The organization owner email is already verified.');
        }

        $owner->notify(new OrganizationOwnerEmailVerificationNotification($organization));

        return back()->with('status', "A verification email has been sent to {$owner->email}.");
    }

    public function verifyOwnerEmail(Organization $organization, string $hash): RedirectResponse
    {
        $owner = $organization->owner;
        abort_unless($owner && hash_equals(sha1($owner->email), $hash), 403);
        if (! $owner->email_verified_at) {
            $owner->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->route('login')->with('status', 'Organization owner email verified. You can now sign in.');
    }

    public function toggleStatus(Organization $organization): JsonResponse
    {
        Gate::authorize('update', $organization);
        $status = DB::transaction(function () use ($organization): string {
            $lockedOrganization = Organization::query()->lockForUpdate()->findOrFail($organization->id);
            $nextStatus = $lockedOrganization->status === 'active' ? 'inactive' : 'active';
            $lockedOrganization->update(['status' => $nextStatus]);

            return $nextStatus;
        });

        return response()->json([
            'status' => $status,
            'label' => ucfirst($status),
            'message' => "Organization {$status}.",
        ]);
    }

    public function destroy(Organization $organization, OrganizationLogoManager $logos): RedirectResponse
    {
        Gate::authorize('delete', $organization);
        $logoPath = $organization->logo_path;
        DB::transaction(function () use ($organization): void {
            $lockedOrganization = Organization::query()->lockForUpdate()->findOrFail($organization->id);
            $lockedOrganization->users()->detach();
            $lockedOrganization->delete();
        });
        $logos->delete($logoPath);

        return redirect()->route('organizations.index')->with('status', 'Organization deleted. Associated user accounts and vCards were retained.');
    }

    private function splitName(?string $name): array
    {
        $parts = preg_split('/\s+/', trim((string) $name), 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function ensureManageableMember(Organization $organization, User $member): User
    {
        $organizationMember = $organization->users()->whereKey($member->id)->firstOrFail();
        abort_if(
            $member->id === $organization->owner_user_id || $organizationMember->pivot->role === 'owner',
            403,
        );

        return $organizationMember;
    }
}
