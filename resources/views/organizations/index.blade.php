@extends('layouts.app')
@section('title', 'Organizations')
@section('breadcrumb')<span>Organizations</span>@endsection
@section('heading', 'Organizations')
@section('subheading', 'Manage organizations, owner accounts, and plan assignments.')
@section('heading-action')
<a class="button button-primary" href="{{ route('organizations.create') }}"><i class="fa-solid fa-plus"></i> Add organization</a>
@endsection
@section('content')
<section class="panel table-panel">
    <form method="GET" action="{{ route('organizations.index') }}" class="filter-bar user-filter-bar">
        <label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input name="search" value="{{ request('search') }}" placeholder="Search organizations..."></label>
        <select name="plan_id" class="select-field">
            <option value="">All plans</option>
            <option value="none" @selected(request('plan_id') === 'none')>No Plan</option>
            @foreach($plans as $plan)<option value="{{ $plan->id }}" @selected((string) request('plan_id') === (string) $plan->id)>{{ $plan->name }}</option>@endforeach
        </select>
        <select name="verification" class="select-field">
            <option value="">All verification states</option>
            <option value="verified" @selected(request('verification') === 'verified')>Verified</option>
            <option value="unverified" @selected(request('verification') === 'unverified')>Not verified</option>
        </select>
        <select name="status" class="select-field">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <button class="button button-outline button-small" type="submit">Filter</button>
        @if(request()->hasAny(['search', 'plan_id', 'verification', 'status']))<a class="text-link" href="{{ route('organizations.index') }}">Clear</a>@endif
    </form>

    @if($organizations->isEmpty())
        <div class="empty-state"><span><i class="fa-regular fa-building"></i></span><strong>No organizations found</strong><p>Add an organization to manage its owner account and plan.</p><a class="button button-primary button-small" href="{{ route('organizations.create') }}">Add organization</a></div>
    @else
        <div class="table-scroll"><table>
            <thead><tr><th>ID</th><th>ORGANIZATION</th><th>CURRENT PLAN</th><th>EMAIL VERIFIED</th><th>STATUS</th><th>CREATED</th><th class="actions-heading">ACTIONS</th></tr></thead>
            <tbody>
            @foreach($organizations as $organization)
                <tr>
                    <td>{{ $organization->id }}</td>
                    <td><strong>{{ $organization->name }}</strong><small class="table-subtext">{{ $organization->email ?: 'No organization email' }}</small></td>
                    <td>{{ $organization->plan?->name ?? 'No Plan' }}</td>
                    <td><span class="status-badge {{ $organization->owner?->email_verified_at ? 'status-active' : 'status-inactive' }}">{{ $organization->owner?->email_verified_at ? 'Verified' : 'Not Verified' }}</span></td>
                    <td><div class="status-control"><button type="button" class="toggle-switch" role="switch" aria-checked="{{ $organization->status === 'active' ? 'true' : 'false' }}" aria-label="Toggle {{ $organization->name }} status" data-async-toggle data-toggle-url="{{ route('organizations.status', $organization) }}" data-toggle-kind="status"><span></span></button><span class="status-badge {{ $organization->status === 'active' ? 'status-active' : 'status-inactive' }}" data-toggle-label>{{ ucfirst($organization->status) }}</span></div></td>
                    <td class="date-cell">{{ $organization->created_at->format('M j, Y') }}</td>
                    <td><div class="row-actions">
                        <a class="icon-action" href="{{ route('organizations.show', $organization) }}" title="View organization"><i class="fa-regular fa-eye"></i></a>
                        <a class="icon-action" href="{{ route('organizations.edit', $organization) }}" title="Edit organization"><i class="fa-regular fa-pen-to-square"></i></a>
                        <form method="POST" action="{{ route('organizations.destroy', $organization) }}" data-confirm="Delete {{ $organization->name }}? Its user accounts and vCards will be retained but disconnected.">@csrf @method('DELETE')<button class="icon-action action-danger" type="submit" title="Delete organization"><i class="fa-regular fa-trash-can"></i></button></form>
                    </div></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        <div class="pagination-wrap"><span>Showing {{ $organizations->firstItem() }}–{{ $organizations->lastItem() }} of {{ $organizations->total() }} organizations</span>{{ $organizations->links() }}</div>
    @endif
</section>
@endsection
