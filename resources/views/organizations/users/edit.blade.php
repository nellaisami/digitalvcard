@extends('layouts.app')
@section('title', 'Edit organization user')
@section('breadcrumb')
<a href="{{ route('organizations.mine') }}">My organization</a><i class="fa-solid fa-chevron-right"></i><a href="{{ route('organizations.show', $organization) }}">{{ $organization->name }}</a><i class="fa-solid fa-chevron-right"></i><span>Edit user</span>
@endsection
@section('heading', 'Edit organization user')
@section('subheading', 'Change this user’s organization role or membership status.')
@section('content')
<form method="POST" action="{{ route('organizations.users.update', [$organization, $member]) }}" class="panel form-panel compact-form">
    @csrf @method('PATCH')
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-pen-to-square"></i></span><div><h2>{{ $member->name }}</h2><p>{{ $member->email }} · Changes apply only to this organization</p></div></div>
    <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="form-field"><label for="organization_role">Organization role <span>*</span></label><select id="organization_role" name="organization_role" required><option value="member" @selected(old('organization_role', $member->pivot->role) === 'member')>Member</option><option value="manager" @selected(old('organization_role', $member->pivot->role) === 'manager')>Manager</option></select>@error('organization_role')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="status">Organization status <span>*</span></label><select id="status" name="status" required><option value="active" @selected(old('status', $member->pivot->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $member->pivot->status) === 'inactive')>Inactive</option></select>@error('status')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="form-actions"><a href="{{ route('organizations.show', $organization) }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-check"></i> Save changes</button></div>
</form>
@endsection
