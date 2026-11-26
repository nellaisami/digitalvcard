@extends('layouts.app')
@section('title', 'Tax Settings')
@section('breadcrumb')
<a href="{{ route('plans.index') }}">Plans</a><i class="fa-solid fa-chevron-right"></i><span>Tax settings</span>
@endsection
@section('heading', 'Tax settings')
@section('subheading', 'Configure tax rates applied to current plan price estimates.')
@section('heading-action')
<div class="heading-actions"><a class="button button-outline" href="{{ route('plans.index') }}"><i class="fa-solid fa-arrow-left"></i> Plans</a><a class="button button-primary" href="{{ route('taxes.create') }}"><i class="fa-solid fa-plus"></i> Add tax</a></div>
@endsection
@section('content')
<section class="panel table-panel">
    <form method="GET" action="{{ route('taxes.index') }}" class="filter-bar">
        <label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input name="search" value="{{ request('search') }}" placeholder="Search taxes..."></label>
        <select name="status" class="select-field"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select>
        <button class="button button-outline button-small" type="submit">Search</button>
        @if(request()->hasAny(['search', 'status']))<a class="text-link" href="{{ route('taxes.index') }}">Clear</a>@endif
    </form>
    @if($taxes->isEmpty())
        <div class="empty-state"><span><i class="fa-solid fa-percent"></i></span><strong>No taxes found</strong><p>Add a tax rate to include it in plan price estimates.</p><a class="button button-primary button-small" href="{{ route('taxes.create') }}">Add tax</a></div>
    @else
        <div class="table-scroll"><table><thead><tr><th>ID</th><th>TAX NAME</th><th>TAX RATE</th><th>STATUS</th><th class="actions-heading">ACTIONS</th></tr></thead><tbody>
        @foreach($taxes as $tax)
            <tr>
                <td>{{ $tax->id }}</td>
                <td><strong>{{ $tax->tax_name }}</strong></td>
                <td>{{ number_format((float) $tax->tax_value, 4) }}%</td>
                <td><div class="status-control"><button type="button" class="toggle-switch" role="switch" aria-checked="{{ $tax->status === 'active' ? 'true' : 'false' }}" aria-label="Toggle {{ $tax->tax_name }} status" data-async-toggle data-toggle-url="{{ route('taxes.status', $tax) }}" data-toggle-kind="status"><span></span></button><span class="status-badge {{ $tax->status === 'active' ? 'status-active' : 'status-inactive' }}" data-toggle-label>{{ ucfirst($tax->status) }}</span></div></td>
                <td><div class="row-actions">
                    <a class="icon-action" href="{{ route('taxes.edit', $tax) }}" title="Edit tax"><i class="fa-regular fa-pen-to-square"></i></a>
                    <form method="POST" action="{{ route('taxes.destroy', $tax) }}" data-confirm="Delete {{ $tax->tax_name }}? Existing transaction records are not changed.">@csrf @method('DELETE')<button class="icon-action action-danger" type="submit" title="Delete tax"><i class="fa-regular fa-trash-can"></i></button></form>
                </div></td>
            </tr>
        @endforeach
        </tbody></table></div>
        <div class="pagination-wrap"><span>Showing {{ $taxes->firstItem() }}–{{ $taxes->lastItem() }} of {{ $taxes->total() }} taxes</span>{{ $taxes->links() }}</div>
    @endif
</section>
@endsection
