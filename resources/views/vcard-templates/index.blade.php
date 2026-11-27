@extends('layouts.app')
@section('title', 'vCard Templates')
@section('breadcrumb')<span>vCard Templates</span>@endsection
@section('heading', 'vCard templates')
@section('subheading', 'Manage the designs Super Admins can include in subscription plans.')
@section('heading-action')
<a class="button button-primary" href="{{ route('vcard-templates.create') }}"><i class="fa-solid fa-plus"></i> Add template</a>
@endsection
@section('content')
<section class="panel table-panel">
    <form method="GET" action="{{ route('vcard-templates.index') }}" class="filter-bar">
        <label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input name="search" value="{{ request('search') }}" placeholder="Search templates..."></label>
        <button class="button button-outline button-small" type="submit">Search</button>
        @if(request()->hasAny(['search']))<a class="text-link" href="{{ route('vcard-templates.index') }}">Clear</a>@endif
    </form>
    @if($templates->isEmpty())
        <div class="empty-state"><span><i class="fa-regular fa-images"></i></span><strong>No vCard templates found</strong><p>Add a template with a preview image before assigning it to plans.</p><a class="button button-primary button-small" href="{{ route('vcard-templates.create') }}">Add template</a></div>
    @else
        <div class="table-scroll"><table><thead><tr><th>TEMPLATE NAME</th><th>PREVIEW IMAGE</th><th>PREVIEW URL</th><th>USED COUNT</th><th class="actions-heading">ACTIONS</th></tr></thead><tbody>
        @foreach($templates as $template)
            <tr>
                <td><strong>{{ $template->name }}</strong></td>
                <td><img class="template-table-preview" src="{{ $template->image_url }}" alt="{{ $template->name }} template preview" data-template-image></td>
                <td><a href="{{ route('vcard-templates.preview', $template) }}" target="_blank" rel="noopener noreferrer">Open preview</a></td>
                <td>{{ $template->users_count }}</td>
                <td><div class="row-actions">
                    <a class="icon-action" href="{{ route('vcard-templates.edit', $template) }}" title="Edit template"><i class="fa-regular fa-pen-to-square"></i></a>
                    <form method="POST" action="{{ route('vcard-templates.destroy', $template) }}" data-confirm="{{ $template->plans_count ? 'Remove this template from assigned plans before deleting it.' : 'Delete template '.$template->name.'? This cannot be undone.' }}">@csrf @method('DELETE')<button class="icon-action action-danger" type="submit" title="{{ $template->plans_count ? 'Remove from assigned plans before deleting' : 'Delete template' }}" @disabled($template->plans_count > 0)><i class="fa-regular fa-trash-can"></i></button></form>
                </div></td>
            </tr>
        @endforeach
        </tbody></table></div>
        <div class="pagination-wrap"><span>Showing {{ $templates->firstItem() }}–{{ $templates->lastItem() }} of {{ $templates->total() }} templates</span>{{ $templates->links() }}</div>
    @endif
</section>
@endsection
