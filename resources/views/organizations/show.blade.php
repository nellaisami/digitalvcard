@extends('layouts.app')
@section('title', $organization->name)
@section('breadcrumb')
@if(auth()->user()->role === 'superadmin')<a href="{{ route('organizations.index') }}">Organizations</a>@else<a href="{{ route('organizations.mine') }}">My organization</a>@endif<i class="fa-solid fa-chevron-right"></i><span>{{ $organization->name }}</span>
@endsection
@section('heading', $organization->name)
@section('subheading', 'Organization, owner, subscription, members, and vCards.')
@section('heading-action')
@if(auth()->user()->role === 'superadmin')<a class="button button-primary" href="{{ route('organizations.edit', $organization) }}"><i class="fa-regular fa-pen-to-square"></i> Edit organization</a>@endif
@endsection
@section('content')
<div class="plan-detail-grid">
    <section class="panel plan-detail-card">
        <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-building"></i></span><div><h2>Organization information</h2><p>Organization account details</p></div></div>
        <dl class="plan-detail-list">
            <div><dt>Name</dt><dd>{{ $organization->name }}</dd></div>
            <div><dt>Email</dt><dd>{{ $organization->email ?: '—' }}</dd></div>
            <div><dt>Status</dt><dd><span class="status-badge {{ $organization->status === 'active' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($organization->status) }}</span></dd></div>
            <div><dt>Created</dt><dd>{{ $organization->created_at->format('M j, Y') }}</dd></div>
            <div><dt>Updated</dt><dd>{{ $organization->updated_at->format('M j, Y') }}</dd></div>
        </dl>
    </section>

    <section class="panel plan-detail-card">
        <div class="form-section-title"><span class="form-section-icon form-icon-purple"><i class="fa-solid fa-user-tie"></i></span><div><h2>Owner information</h2><p>Account used to sign in to the dashboard</p></div></div>
        @if($organization->owner)
            <dl class="plan-detail-list">
                <div><dt>Name</dt><dd>{{ $organization->owner->name }}</dd></div>
                <div><dt>Email</dt><dd>{{ $organization->owner->email }}</dd></div>
                <div><dt>Contact number</dt><dd>{{ $organization->owner->contact_no ?: '—' }}</dd></div>
                <div><dt>Email verification</dt><dd><span class="status-badge {{ $organization->owner->email_verified_at ? 'status-active' : 'status-inactive' }}">{{ $organization->owner->email_verified_at ? 'Verified' : 'Not Verified' }}</span></dd></div>
            </dl>
            @if(auth()->user()->role === 'superadmin' && ! $organization->owner->email_verified_at)
                <form method="POST" action="{{ route('organizations.verification.resend', $organization) }}" class="plan-detail-footer">@csrf<button class="button button-outline button-small" type="submit">Resend verification email</button></form>
            @endif
        @else
            <p class="plan-muted-copy">No owner account is linked. Edit this organization to create one.</p>
        @endif
    </section>

    <section class="panel plan-detail-card">
        <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-layer-group"></i></span><div><h2>Subscription information</h2><p>Current organization plan</p></div></div>
        @if($organization->plan)
            <dl class="plan-detail-list">
                <div><dt>Current plan</dt><dd>{{ $organization->plan->name }}</dd></div>
                <div><dt>Price</dt><dd>{{ strtoupper(config('app.currency', 'USD')) }} {{ number_format((float) $organization->plan->price, 2) }}</dd></div>
                <div><dt>Frequency</dt><dd>{{ ucfirst($organization->plan->frequency) }}</dd></div>
                <div><dt>Plan status</dt><dd><span class="status-badge {{ $organization->plan->status === 'active' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($organization->plan->status) }}</span></dd></div>
            </dl>
        @else
            <p class="plan-muted-copy">No Plan assigned.</p>
        @endif
    </section>

    @if(auth()->user()->role === 'superadmin' && $organization->owner)
        <section class="panel plan-detail-card">
            <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-key"></i></span><div><h2>Update owner password</h2><p>This changes the owner's existing user account password</p></div></div>
            <form method="POST" action="{{ route('organizations.owner.password', $organization) }}" class="form-field">
                @csrf @method('PATCH')
                <label for="owner_password">New password</label><input id="owner_password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                @error('password')<small class="field-error">{{ $message }}</small>@enderror
                <label for="owner_password_confirmation">Confirm new password</label><input id="owner_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                <button class="button button-outline button-small" type="submit">Update password</button>
            </form>
        </section>
    @endif
</div>

<section class="panel table-panel organization-table-section">
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-users"></i></span><div><h2>Organization users</h2><p>Accounts associated with this organization</p></div>@if($canManageUsers)<a class="button button-primary button-small" href="{{ route('organizations.users.create', $organization) }}"><i class="fa-solid fa-plus"></i> Add user</a>@endif</div>
    @if($organization->users->isEmpty())
        <div class="empty-state"><strong>No organization users</strong><p>No user accounts are associated with this organization.</p></div>
    @else
        <div class="table-scroll"><table><thead><tr><th>USER</th><th>EMAIL</th><th>ROLE</th><th>STATUS</th>@if($canManageUsers)<th class="actions-heading">ACTIONS</th>@endif</tr></thead><tbody>
        @foreach($organization->users as $user)
            @php($protectedMember = $user->id === $organization->owner_user_id || $user->pivot->role === 'owner')
            <tr>
                <td><strong>{{ $user->name }}</strong></td><td>{{ $user->email }}</td><td>{{ ucfirst($user->pivot->role) }}</td>
                <td>@if($canManageUsers && !$protectedMember)<div class="status-control"><button type="button" class="toggle-switch" role="switch" aria-checked="{{ $user->pivot->status === 'active' ? 'true' : 'false' }}" aria-label="Toggle {{ $user->name }} organization status" data-async-toggle data-toggle-url="{{ route('organizations.users.status', [$organization, $user]) }}" data-toggle-kind="status"><span></span></button><span class="status-badge {{ $user->pivot->status === 'active' ? 'status-active' : 'status-inactive' }}" data-toggle-label>{{ ucfirst($user->pivot->status) }}</span></div>@else<span class="status-badge {{ $user->pivot->status === 'active' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($user->pivot->status) }}</span>@endif</td>
                @if($canManageUsers)<td>@if(!$protectedMember)<a class="icon-action" href="{{ route('organizations.users.edit', [$organization, $user]) }}" title="Edit organization user"><i class="fa-regular fa-pen-to-square"></i></a><form method="POST" action="{{ route('organizations.users.destroy', [$organization, $user]) }}" class="inline-form" data-confirm="Remove {{ $user->name }} from this organization? Their account and vCards will be retained.">@csrf @method('DELETE')<button class="icon-action action-danger" type="submit" title="Remove from organization"><i class="fa-regular fa-trash-can"></i></button></form>@endif</td>@endif
            </tr>
        @endforeach
        </tbody></table></div>
    @endif
</section>

<section class="panel table-panel organization-table-section">
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-address-card"></i></span><div><h2>vCards</h2><p>Cards owned by members of this organization</p></div></div>
    @if($vcards->isEmpty())
        <div class="empty-state"><strong>No vCards</strong><p>No vCards are associated with this organization yet.</p></div>
    @else
        <div class="table-scroll"><table><thead><tr><th>TITLE</th><th>OWNER</th><th>STATUS</th><th>CREATED</th></tr></thead><tbody>
        @foreach($vcards as $vcard)
            <tr><td><strong>{{ $vcard->title }}</strong></td><td>{{ $vcard->user?->name ?? '—' }}</td><td><span class="status-badge {{ $vcard->status === 'active' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($vcard->status) }}</span></td><td class="date-cell">{{ $vcard->created_at->format('M j, Y') }}</td></tr>
        @endforeach
        </tbody></table></div>
    @endif
</section>
@endsection
