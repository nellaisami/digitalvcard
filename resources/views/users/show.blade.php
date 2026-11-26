@extends('layouts.app')
@php($adminMode = ($mode ?? 'user') === 'admin')
@php($routePrefix = $adminMode ? 'admin' : 'users')
@section('title', ($adminMode ? 'Admin' : 'User').' details')
@section('breadcrumb')
<a href="{{ route($routePrefix.'.index') }}">{{ $adminMode ? 'Admin' : 'Users' }}</a><i class="fa-solid fa-chevron-right"></i><span>Details</span>
@endsection
@section('heading', ($adminMode ? 'Admin' : 'User').' details')
@section('subheading', 'Profile, plan, and access information for this account.')
@section('heading-action')<a class="button button-primary" href="{{ route($routePrefix.'.edit', $user) }}"><i class="fa-regular fa-pen-to-square"></i> Edit {{ $adminMode ? 'admin' : 'user' }}</a>@endsection
@section('content')
<section class="panel detail-panel">
    <div class="detail-profile"><img class="avatar avatar-large avatar-photo" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }} profile photo"><div><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p><span class="status-badge {{ $user->status === 'active' ? 'status-active' : 'status-inactive' }}"><i></i>{{ ucfirst($user->status) }}</span></div></div>
    <div class="detail-grid">
        <div><small>ROLE</small><strong>{{ ucfirst($user->role) }}</strong></div>
        <div><small>ASSIGNED PLAN</small><strong>{{ $user->plan?->name ?? 'No plan assigned' }}</strong></div>
        <div><small>PHONE</small><strong>{{ $user->contact_no ?: 'Not provided' }}</strong></div>
        <div><small>EMAIL VERIFICATION</small><strong>{{ $user->email_verified_at?->format('M j, Y') ?? 'Not verified' }}</strong></div>
        <div><small>ADDRESS</small><strong>{{ collect([$user->address, $user->city, $user->country, $user->postal_code])->filter()->join(', ') ?: 'Not provided' }}</strong></div>
        <div><small>JOINED</small><strong>{{ $user->created_at->format('M j, Y') }}</strong></div>
    </div>
    <a class="button button-outline" href="{{ route($routePrefix.'.index') }}"><i class="fa-solid fa-arrow-left"></i> Back to {{ $adminMode ? 'admins' : 'users' }}</a>
</section>
@endsection
