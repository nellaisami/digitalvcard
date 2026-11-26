@extends('layouts.app')
@section('title', 'Add organization user')
@section('breadcrumb')
<a href="{{ route('organizations.show', $organization) }}">{{ $organization->name }}</a><i class="fa-solid fa-chevron-right"></i><span>Add user</span>
@endsection
@section('heading', 'Add organization user')
@section('subheading', 'Create a new login or add an existing standard user account.')
@section('content')
<div class="plan-detail-grid">
    <section class="panel form-panel compact-form">
        <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-user-plus"></i></span><div><h2>Create a new account</h2><p>A verification email will be sent to the new user</p></div></div>
        <form method="POST" action="{{ route('organizations.users.store', $organization) }}">
            @csrf
            <input type="hidden" name="account_type" value="create">
            <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="form-field"><label for="new_name">Full name <span>*</span></label><input id="new_name" name="name" value="{{ old('account_type') === 'create' ? old('name') : '' }}" maxlength="255" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="form-field"><label for="new_email">Email address <span>*</span></label><input id="new_email" name="email" type="email" value="{{ old('account_type') === 'create' ? old('email') : '' }}" maxlength="255" required>@error('email')@if(old('account_type') === 'create')<small class="field-error">{{ $message }}</small>@endif @enderror</div>
                <div class="form-field"><label for="new_contact_no">Contact number</label><input id="new_contact_no" name="contact_no" type="tel" value="{{ old('account_type') === 'create' ? old('contact_no') : '' }}" maxlength="30">@error('contact_no')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="form-field"><label for="new_password">Password <span>*</span></label><input id="new_password" name="password" type="password" autocomplete="new-password" minlength="8" required>@error('password')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="form-field"><label for="new_password_confirmation">Confirm password <span>*</span></label><input id="new_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required></div>
                <div class="form-field"><label for="new_role">Organization role <span>*</span></label><select id="new_role" name="organization_role" required><option value="member" @selected(old('account_type') === 'create' ? old('organization_role', 'member') === 'member' : true)>Member</option><option value="manager" @selected(old('account_type') === 'create' && old('organization_role') === 'manager')>Manager</option></select>@error('organization_role')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="form-field"><label for="new_status">Organization status <span>*</span></label><select id="new_status" name="status" required><option value="active" @selected(old('account_type') === 'create' ? old('status', 'active') === 'active' : true)>Active</option><option value="inactive" @selected(old('account_type') === 'create' && old('status') === 'inactive')>Inactive</option></select>@error('status')<small class="field-error">{{ $message }}</small>@enderror</div>
            </div>
            <div class="form-actions"><a href="{{ route('organizations.show', $organization) }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-user-plus"></i> Create user</button></div>
        </form>
    </section>

    <section class="panel form-panel compact-form">
        <div class="form-section-title"><span class="form-section-icon form-icon-purple"><i class="fa-solid fa-user-check"></i></span><div><h2>Add an existing account</h2><p>Add a standard user already registered in this workspace</p></div></div>
        <form method="POST" action="{{ route('organizations.users.store', $organization) }}">
            @csrf
            <input type="hidden" name="account_type" value="existing">
            <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="form-field"><label for="existing_email">User email address <span>*</span></label><input id="existing_email" name="email" type="email" value="{{ old('account_type') === 'existing' ? old('email') : '' }}" maxlength="255" required>@error('email')@if(old('account_type') === 'existing')<small class="field-error">{{ $message }}</small>@endif @enderror</div>
                <div class="form-field"><label for="existing_role">Organization role <span>*</span></label><select id="existing_role" name="organization_role" required><option value="member" @selected(old('account_type') === 'existing' ? old('organization_role', 'member') === 'member' : true)>Member</option><option value="manager" @selected(old('account_type') === 'existing' && old('organization_role') === 'manager')>Manager</option></select>@error('organization_role')@if(old('account_type') === 'existing')<small class="field-error">{{ $message }}</small>@endif @enderror</div>
                <div class="form-field"><label for="existing_status">Organization status <span>*</span></label><select id="existing_status" name="status" required><option value="active" @selected(old('account_type') === 'existing' ? old('status', 'active') === 'active' : true)>Active</option><option value="inactive" @selected(old('account_type') === 'existing' && old('status') === 'inactive')>Inactive</option></select>@error('status')@if(old('account_type') === 'existing')<small class="field-error">{{ $message }}</small>@endif @enderror</div>
            </div>
            <div class="form-actions"><a href="{{ route('organizations.show', $organization) }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-user-check"></i> Add existing user</button></div>
        </form>
    </section>
</div>
@endsection
