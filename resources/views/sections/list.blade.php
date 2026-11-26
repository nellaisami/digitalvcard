@extends('layouts.app')
@section('title', $title)
@section('breadcrumb')<span>{{ $title }}</span>@endsection
@section('heading', $title)
@section('subheading', $description)
@if(isset($createUrl))@section('heading-action')<a class="button button-primary" href="{{ $createUrl }}"><i class="fa-solid fa-plus"></i> {{ $createLabel }}</a>@endsection @endif
@section('content')
<section class="panel table-panel">
    @if(count($rows))<div class="table-scroll"><table><thead><tr>@foreach($headers as $header)<th>{{ strtoupper($header) }}</th>@endforeach @if(isset($createUrl))<th class="actions-heading">ACTIONS</th>@endif</tr></thead><tbody>@foreach($rows as $row)<tr>@foreach($row as $value)<td>{{ $value }}</td>@endforeach @if(isset($createUrl))<td><a class="icon-action" href="{{ route('users.index') }}" title="Manage accounts"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></td>@endif</tr>@endforeach</tbody></table></div>
    @else<div class="empty-state"><span><i class="fa-solid fa-folder-open"></i></span><strong>No {{ strtolower($title) }} yet</strong><p>There’s nothing to show here just yet. Check back later.</p>@if(isset($createUrl))<a class="button button-primary button-small" href="{{ $createUrl }}">{{ $createLabel }}</a>@endif</div>@endif
</section>
@endsection
