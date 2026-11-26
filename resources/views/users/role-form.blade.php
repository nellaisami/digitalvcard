@extends('layouts.app')
@section('title', 'Manage user role')
@section('breadcrumb')
<a href="{{ route('users.index') }}">Users</a><i class="fa-solid fa-chevron-right"></i><span>Manage role</span>
@endsection
@section('heading', 'Manage user role')
@section('subheading', 'Role changes are restricted to Super Admins and are managed separately from profile edits.')
@section('content')
<form method="POST" action="{{ route('users.role.update', $user) }}" class="panel form-panel compact-form">
    @csrf @method('PATCH')
    <div class="detail-profile"><img class="avatar avatar-large avatar-photo" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }} profile photo"><div><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p></div></div>
    <div class="form-field role-field"><label for="role">Role</label><select id="role" name="role" required>@foreach(['admin' => 'Admin', 'manager' => 'Manager', 'editor' => 'Editor', 'staff' => 'Staff'] as $value => $label)<option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="form-actions"><a class="button button-outline" href="{{ route('users.index') }}">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-shield-halved"></i> Save role</button></div>
</form>
@endsection
