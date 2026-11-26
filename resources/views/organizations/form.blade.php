@extends('layouts.app')
@php
    $editing = $organization->exists;
    $needsOwner = ! $owner;
@endphp
@section('title', ($editing ? 'Edit' : 'Add').' organization')
@section('breadcrumb')
<a href="{{ route('organizations.index') }}">Organizations</a><i class="fa-solid fa-chevron-right"></i><span>{{ $editing ? 'Edit' : 'Add' }} organization</span>
@endsection
@section('heading', $editing ? 'Edit organization' : 'Add organization')
@section('subheading', 'Manage organization details, its owner account, and plan assignment.')
@section('content')
<form method="POST" action="{{ $editing ? route('organizations.update', $organization) : route('organizations.store') }}" class="panel form-panel compact-form">
    @csrf @if($editing) @method('PUT') @endif

    <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-building"></i></span><div><h2>Organization information</h2><p>Set the public organization details and account status</p></div></div>
    <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="form-field"><label for="name">Organization name <span>*</span></label><input id="name" name="name" value="{{ old('name', $organization->name) }}" maxlength="255" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="email">Organization email <span>*</span></label><input id="email" name="email" type="email" value="{{ old('email', $organization->email) }}" maxlength="255" required>@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="status">Status <span>*</span></label><select id="status" name="status" required><option value="active" @selected(old('status', $organization->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $organization->status) === 'inactive')>Inactive</option></select>@error('status')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>

    <div class="form-divider"></div>
    <div class="form-section-title"><span class="form-section-icon form-icon-purple"><i class="fa-solid fa-user-tie"></i></span><div><h2>Organization owner</h2><p>Owner credentials use the existing user account system</p></div></div>
    <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="form-field"><label for="owner_first_name">First name <span>*</span></label><input id="owner_first_name" name="owner_first_name" value="{{ old('owner_first_name', $ownerFirstName) }}" maxlength="100" required>@error('owner_first_name')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="owner_last_name">Last name <span>*</span></label><input id="owner_last_name" name="owner_last_name" value="{{ old('owner_last_name', $ownerLastName) }}" maxlength="100" required>@error('owner_last_name')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="owner_email">Owner email <span>*</span></label><input id="owner_email" name="owner_email" type="email" value="{{ old('owner_email', $owner?->email) }}" maxlength="255" required>@error('owner_email')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="contact_no">Contact number</label><input id="contact_no" name="contact_no" type="tel" value="{{ old('contact_no', $owner?->contact_no) }}" maxlength="30" placeholder="+1 555 123 4567">@error('contact_no')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>

    @if($needsOwner)
        <div class="form-divider"></div>
        <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-key"></i></span><div><h2>Login credentials</h2><p>The owner will sign in through the existing email OTP flow</p></div></div>
        <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="form-field"><label for="password">Password <span>*</span></label><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>@error('password')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-field"><label for="password_confirmation">Confirm password <span>*</span></label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required></div>
        </div>
    @endif

    <div class="form-divider"></div>
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-layer-group"></i></span><div><h2>Plan assignment</h2><p>Only active plans can be newly assigned</p></div></div>
    <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="form-field"><label for="plan_id">Plan</label><select id="plan_id" name="plan_id"><option value="">No Plan</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected((string) old('plan_id', $organization->plan_id) === (string) $plan->id)>{{ $plan->name }} — {{ strtoupper(config('app.currency', 'USD')) }} {{ number_format((float) $plan->price, 2) }} / {{ ucfirst($plan->frequency) }}</option>@endforeach</select>@error('plan_id')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>

    <div class="form-actions"><a href="{{ $editing ? route('organizations.show', $organization) : route('organizations.index') }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-check"></i>{{ $editing ? 'Save changes' : 'Create organization' }}</button></div>
</form>
@endsection
