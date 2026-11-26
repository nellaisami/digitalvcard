@extends('layouts.app')
@section('title', 'Plan Management')
@section('breadcrumb')<span>Plan Management</span>@endsection
@section('heading', 'Plan management')
@section('subheading', 'Configure subscription pricing, access features, limits, and taxes.')
@section('heading-action')
<div class="heading-actions">
    <a class="button button-outline" href="{{ route('taxes.index') }}"><i class="fa-solid fa-percent"></i> Tax settings</a>
    <a class="button button-primary" href="{{ route('plans.create') }}"><i class="fa-solid fa-plus"></i> Add new plan</a>
</div>
@endsection
@section('content')
<section class="panel table-panel">
    <form method="GET" action="{{ route('plans.index') }}" class="filter-bar">
        <label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input name="search" value="{{ request('search') }}" placeholder="Search plans..."></label>
        <select name="status" class="select-field"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select>
        <button class="button button-outline button-small" type="submit">Search</button>
        @if(request()->hasAny(['search', 'status']))<a class="text-link" href="{{ route('plans.index') }}">Clear</a>@endif
    </form>
    @if($plans->isEmpty())
        <div class="empty-state"><span><i class="fa-solid fa-layer-group"></i></span><strong>No plans found</strong><p>Create a plan to configure pricing and features.</p><a class="button button-primary button-small" href="{{ route('plans.create') }}">Add new plan</a></div>
    @else
        <div class="table-scroll"><table><thead><tr><th>ID</th><th>PLAN NAME</th><th>PRICE</th><th>FREQUENCY</th><th>TEMPLATES</th><th>FEATURES</th><th>ASSIGNED USERS</th><th>STATUS</th><th>DEFAULT PLAN</th><th class="actions-heading">ACTIONS</th></tr></thead><tbody>
        @foreach($plans as $plan)
            <tr>
                <td>{{ $plan->id }}</td>
                <td><strong>{{ $plan->name }}</strong></td>
                <td>{{ strtoupper(config('app.currency', 'USD')) }} {{ number_format((float) $plan->price, 2) }}</td>
                <td>{{ ucfirst($plan->frequency) }}</td>
                <td>{{ $plan->vcard_templates_count }}</td>
                <td>{{ count($plan->features ?? []) }} enabled</td>
                <td>{{ $plan->users_count }}</td>
                <td><div class="status-control"><button type="button" class="toggle-switch" role="switch" aria-checked="{{ $plan->status === 'active' ? 'true' : 'false' }}" aria-label="Toggle {{ $plan->name }} status" data-async-toggle data-toggle-url="{{ route('plans.status', $plan) }}" data-toggle-kind="status"><span></span></button><span class="status-badge {{ $plan->status === 'active' ? 'status-active' : 'status-inactive' }}" data-toggle-label>{{ ucfirst($plan->status) }}</span></div></td>
                <td>
                    @if($plan->is_default)<span class="status-badge status-active">Default</span>
                    @else<form method="POST" action="{{ route('plans.default', $plan) }}">@csrf @method('PATCH')<button class="button button-outline button-small" type="submit" @disabled($plan->status !== 'active')>Set default</button></form>@endif
                </td>
                <td><div class="row-actions">
                    <a class="icon-action" href="{{ route('plans.show', $plan) }}" title="View plan"><i class="fa-regular fa-eye"></i></a>
                    <a class="icon-action" href="{{ route('plans.edit', $plan) }}" title="Edit plan"><i class="fa-regular fa-pen-to-square"></i></a>
                    <form method="POST" action="{{ route('plans.destroy', $plan) }}" data-confirm="{{ $plan->users_count ? 'This plan is assigned to users. Reassign them before deletion.' : 'Delete plan '.$plan->name.'? This cannot be undone.' }}">@csrf @method('DELETE')<button class="icon-action action-danger" type="submit" title="Delete plan" @disabled($plan->users_count > 0)><i class="fa-regular fa-trash-can"></i></button></form>
                </div></td>
            </tr>
        @endforeach
        </tbody></table></div>
        <div class="pagination-wrap"><span>Showing {{ $plans->firstItem() }}–{{ $plans->lastItem() }} of {{ $plans->total() }} plans</span>{{ $plans->links() }}</div>
    @endif
</section>
@endsection
