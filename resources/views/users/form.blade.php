@extends('layouts.app')
@php
    $editing = $user->exists;
    $adminMode = ($mode ?? 'user') === 'admin';
    $routePrefix = $adminMode ? 'admin' : 'users';
    $entityLabel = $adminMode ? 'admin' : 'user';
@endphp
@section('title', ($editing ? 'Edit ' : 'Add ').ucfirst($entityLabel))
@section('breadcrumb')
<a href="{{ route($routePrefix.'.index') }}">{{ ucfirst($entityLabel).'s' }}</a><i class="fa-solid fa-chevron-right"></i>{{ $editing ? 'Edit' : 'Add' }} {{ $entityLabel }}
@endsection
@section('heading', $editing ? 'Edit '.ucfirst($entityLabel) : 'Add '.($adminMode ? 'an admin' : 'a user'))
@section('subheading', $editing ? 'Update profile details and account status.' : 'Create a new account for your workspace.')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route($routePrefix.'.update', $user) : route($routePrefix.'.store') }}" class="panel form-panel">
    @csrf @if($editing) @method('PUT') @endif
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-id-card"></i></span><div><h2>Personal information</h2><p>Basic details for this account</p></div></div>
    <div class="photo-editor">
        <img class="avatar avatar-large avatar-photo" src="{{ $user->profile_photo_url }}" alt="Profile photo preview" data-photo-preview>
        <div class="photo-editor-fields"><label class="button button-outline button-small photo-upload-button" for="profile_photo"><i class="fa-solid fa-arrow-up-from-bracket"></i> Choose photo</label><input id="profile_photo" class="visually-hidden" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-photo-input><small>JPG, PNG, or WebP. Maximum 2 MB; image dimensions up to 4096 × 4096.</small>@error('profile_photo')<small class="field-error">{{ $message }}</small>@enderror
            @if($user->profile_photo)<label class="remove-photo-control"><input type="checkbox" name="remove_photo" value="1" data-photo-remove> Remove current photo</label>@endif
        </div>
    </div>
    <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="form-field"><label for="name">Full name <span>*</span></label><input id="name" name="name" value="{{ old('name', $user->name) }}" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="email">Email address <span>*</span></label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="contact_no">Contact number</label><input id="contact_no" name="contact_no" value="{{ old('contact_no', $user->contact_no) }}"></div>
        <div class="form-field"><label for="address">Address</label><input id="address" name="address" value="{{ old('address', $user->address) }}"></div>
        <div class="form-field"><label for="city">City</label><input id="city" name="city" value="{{ old('city', $user->city) }}"></div>
        <div class="form-field"><label for="country">Country</label><input id="country" name="country" value="{{ old('country', $user->country) }}"></div>
        <div class="form-field"><label for="postal_code">Postal code</label><input id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}"></div>
        @unless($adminMode)
            <div class="form-field"><label for="plan_id">Assigned plan</label><select id="plan_id" name="plan_id"><option value="">No plan</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected((string) old('plan_id', $user->plan_id) === (string) $plan->id)>{{ $plan->name }} — {{ strtoupper(config('app.currency', 'USD')) }} {{ number_format((float) $plan->price, 2) }} / {{ ucfirst($plan->frequency) }}{{ $plan->status === 'active' ? '' : ' (Inactive — current plan)' }}</option>@endforeach</select>@error('plan_id')<small class="field-error">{{ $message }}</small>@enderror</div>
        @endunless
    </div>
    <div class="form-divider"></div>
    <div class="form-section-title"><span class="form-section-icon form-icon-purple"><i class="fa-solid fa-shield-halved"></i></span><div><h2>Account access</h2><p>Set account status and sign-in credentials. Role changes are managed separately.</p></div></div>
    <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="form-field"><label for="status">Status <span>*</span></label><select id="status" name="status" required><option value="active" @selected(old('status', $user->status ?: 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $user->status) === 'inactive')>Inactive</option></select></div>
        <div class="form-field"><label for="password">Password @if(!$editing)<span>*</span>@endif</label><input id="password" name="password" type="password" autocomplete="new-password" {{ $editing ? '' : 'required' }} placeholder="{{ $editing ? 'Leave blank to keep current password' : 'At least 8 characters' }}">@error('password')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
    </div>
    <div class="form-actions"><a href="{{ route($routePrefix.'.index') }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-check"></i>{{ $editing ? 'Save changes' : 'Create '.($adminMode ? 'admin' : 'user') }}</button></div>
</form>
@endsection
