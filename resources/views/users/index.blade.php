@extends('layouts.app')
@section('title', 'Users')
@section('breadcrumb', 'Users')
@section('heading', 'Users')
@section('subheading', 'Manage user profiles, plans, verification, and account status.')
@section('heading-action')<a class="button button-primary" href="{{ route('users.create') }}"><i class="fa-solid fa-plus"></i> Add user</a>@endsection
@section('content')
<section class="panel table-panel">
    <form method="GET" action="{{ route('users.index') }}" class="filter-bar user-filter-bar">
        <label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input name="search" value="{{ request('search') }}" placeholder="Search name, email, or contact..."></label>
        <select name="role" class="select-field"><option value="">All roles</option>@foreach($roles as $role)<option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>@endforeach</select>
        <select name="plan_id" class="select-field"><option value="">All plans</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected((string) request('plan_id') === (string) $plan->id)>{{ $plan->name }}</option>@endforeach</select>
        <select name="verification" class="select-field"><option value="">Any verification</option><option value="verified" @selected(request('verification') === 'verified')>Verified</option><option value="unverified" @selected(request('verification') === 'unverified')>Not verified</option></select>
        <select name="status" class="select-field"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select>
        <button class="button button-outline button-small" type="submit">Filter</button>
        @if(request()->hasAny(['search', 'role', 'plan_id', 'verification', 'status']))<a class="text-link" href="{{ route('users.index') }}">Clear</a>@endif
    </form>
    @if($users->isEmpty())
        <div class="empty-state"><span><i class="fa-solid fa-user-plus"></i></span><strong>No users found</strong><p>Try changing your filters or add a new user.</p><a class="button button-primary button-small" href="{{ route('users.create') }}">Add user</a></div>
    @else
        <div class="table-scroll"><table><thead><tr><th>ID</th><th>PHOTO</th><th>FULL NAME</th><th>EMAIL ADDRESS</th><th>CONTACT</th><th>EMAIL VERIFICATION</th><th>ROLE</th><th>PLAN</th><th>FREQUENCY</th><th>STATUS</th><th>CREATED</th><th class="actions-heading">ACTIONS</th></tr></thead><tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td><img class="avatar avatar-small avatar-photo" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }} profile photo"></td>
                <td><strong>{{ $user->name }}</strong></td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->contact_no ?: '—' }}</td>
                <td><div class="status-control"><button type="button" class="toggle-switch" role="switch" aria-checked="{{ $user->email_verified_at ? 'true' : 'false' }}" aria-label="Toggle {{ $user->name }} email verification" data-async-toggle data-toggle-url="{{ route('users.verification', $user) }}" data-toggle-kind="verification"><span></span></button><span class="status-badge {{ $user->email_verified_at ? 'status-active' : 'status-inactive' }}" data-toggle-label>{{ $user->email_verified_at ? 'Verified' : 'Not verified' }}</span></div></td>
                <td><a class="role-badge role-link" href="{{ route('users.role.edit', $user) }}" title="Manage role">{{ ucfirst($user->role) }} <i class="fa-solid fa-pen"></i></a></td>
                <td>{{ $user->plan?->name ?? '—' }}@if($user->plan)<small class="table-subtext">{{ strtoupper(config('app.currency', 'USD')) }} {{ number_format((float) $user->plan->price, 2) }}</small>@endif</td>
                <td>{{ $user->plan ? ucfirst($user->plan->frequency) : '—' }}</td>
                <td><div class="status-control"><button type="button" class="toggle-switch" role="switch" aria-checked="{{ $user->status === 'active' ? 'true' : 'false' }}" aria-label="Toggle {{ $user->name }} account status" data-async-toggle data-toggle-url="{{ route('users.status', $user) }}" data-toggle-kind="status"><span></span></button><span class="status-badge {{ $user->status === 'active' ? 'status-active' : 'status-inactive' }}" data-toggle-label>{{ ucfirst($user->status) }}</span></div></td>
                <td class="date-cell">{{ $user->created_at->format('M j, Y') }}</td>
                <td><div class="row-actions">
                    <a class="icon-action" href="{{ route('users.show', $user) }}" title="View user"><i class="fa-regular fa-eye"></i></a>
                    <a class="icon-action" href="{{ route('users.edit', $user) }}" title="Edit user"><i class="fa-regular fa-pen-to-square"></i></a>
                    <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="Delete {{ $user->name }}? This cannot be undone.">@csrf @method('DELETE')<button class="icon-action action-danger" type="submit" title="Delete user"><i class="fa-regular fa-trash-can"></i></button></form>
                </div></td>
            </tr>
        @endforeach
        </tbody></table></div>
        <div class="pagination-wrap"><span>Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users</span>{{ $users->links() }}</div>
    @endif
</section>
@endsection
