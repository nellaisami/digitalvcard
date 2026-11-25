@extends('layouts.app')
@section('title', 'Admin Management')
@section('breadcrumb', 'Admin')
@section('heading', 'Admin management')
@section('subheading', 'Manage administrator accounts and access to your workspace.')
@section('heading-action')<a class="button button-primary" href="{{ route('admin.create') }}"><i class="fa-solid fa-plus"></i> Add admin</a>@endsection
@section('content')
<section class="panel table-panel">
    <form method="GET" action="{{ route('admin.index') }}" class="filter-bar">
        <label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input name="search" value="{{ request('search') }}" placeholder="Search admins..."></label>
        <select name="status" class="select-field"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select>
        <button class="button button-outline button-small" type="submit">Filter</button>
        @if(request()->hasAny(['search', 'status']))<a class="text-link" href="{{ route('admin.index') }}">Clear</a>@endif
    </form>
    @if($admins->isEmpty())
        <div class="empty-state"><span><i class="fa-solid fa-user-shield"></i></span><strong>No admins found</strong><p>Add an admin account or adjust your search.</p><a class="button button-primary button-small" href="{{ route('admin.create') }}">Add admin</a></div>
    @else
        <div class="table-scroll"><table><thead><tr><th>ID</th><th>PHOTO</th><th>NAME</th><th>EMAIL</th><th>CONTACT</th><th>EMAIL VERIFICATION</th><th>STATUS</th><th>CREATED</th><th class="actions-heading">ACTIONS</th></tr></thead><tbody>
        @foreach($admins as $admin)
            <tr>
                <td>{{ $admin->id }}</td>
                <td><img class="avatar avatar-small avatar-photo" src="{{ $admin->profile_photo_url }}" alt="{{ $admin->name }} profile photo"></td>
                <td><strong>{{ $admin->name }}</strong></td>
                <td>{{ $admin->email }}</td>
                <td>{{ $admin->contact_no ?: '—' }}</td>
                <td><span class="status-badge {{ $admin->email_verified_at ? 'status-active' : 'status-inactive' }}"><i></i>{{ $admin->email_verified_at ? 'Verified' : 'Not verified' }}</span></td>
                <td><div class="status-control"><button type="button" class="toggle-switch" role="switch" aria-checked="{{ $admin->status === 'active' ? 'true' : 'false' }}" aria-label="Toggle {{ $admin->name }} account status" data-async-toggle data-toggle-url="{{ route('admin.status', $admin) }}" data-toggle-kind="status"><span></span></button><span data-toggle-label>{{ ucfirst($admin->status) }}</span></div></td>
                <td class="date-cell">{{ $admin->created_at->format('M j, Y') }}</td>
                <td><div class="row-actions">
                    <a class="icon-action" href="{{ route('admin.show', $admin) }}" title="View admin"><i class="fa-regular fa-eye"></i></a>
                    <a class="icon-action" href="{{ route('admin.edit', $admin) }}" title="Edit admin"><i class="fa-regular fa-pen-to-square"></i></a>
                    <form method="POST" action="{{ route('admin.destroy', $admin) }}" data-confirm="Delete admin {{ $admin->name }}? This cannot be undone.">@csrf @method('DELETE')<button class="icon-action action-danger" type="submit" title="Delete admin"><i class="fa-regular fa-trash-can"></i></button></form>
                </div></td>
            </tr>
        @endforeach
        </tbody></table></div>
        <div class="pagination-wrap"><span>Showing {{ $admins->firstItem() }}–{{ $admins->lastItem() }} of {{ $admins->total() }} admins</span>{{ $admins->links() }}</div>
    @endif
</section>
@endsection
