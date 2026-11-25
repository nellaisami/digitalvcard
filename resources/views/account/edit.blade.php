@extends('layouts.app')
@section('title', 'Account settings')
@section('breadcrumb', 'Settings')
@section('heading', 'Account settings')
@section('subheading', 'Update your profile and keep your sign-in secure.')
@section('content')
<section class="panel form-panel account-panel">
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-user"></i></span><div><h2>Personal information</h2><p>These details are associated with your account</p></div></div>
    <form method="POST" enctype="multipart/form-data" action="{{ route('account.update') }}">@csrf @method('PUT')
        <div class="photo-editor"><img class="avatar avatar-large avatar-photo" src="{{ $user->profile_photo_url }}" alt="Profile photo preview" data-photo-preview><div class="photo-editor-fields"><label class="button button-outline button-small photo-upload-button" for="profile_photo"><i class="fa-solid fa-arrow-up-from-bracket"></i> Choose photo</label><input id="profile_photo" class="visually-hidden" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-photo-input><small>JPG, PNG, or WebP. Maximum 2 MB; image dimensions up to 4096 × 4096.</small>@error('profile_photo')<small class="field-error">{{ $message }}</small>@enderror
            @if($user->profile_photo)<label class="remove-photo-control"><input type="checkbox" name="remove_photo" value="1" data-photo-remove> Remove current photo</label>@endif
        </div></div>
        <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="form-field"><label for="name">Full name <span>*</span></label><input id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
            <div class="form-field"><label for="email">Email address <span>*</span></label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required><small class="field-hint">Changing your email marks it unverified until the next OTP sign-in.</small></div>
            <div class="form-field"><label for="contact_no">Contact number</label><input id="contact_no" name="contact_no" value="{{ old('contact_no', $user->contact_no) }}"></div>
            <div class="form-field"><label for="address">Address</label><input id="address" name="address" value="{{ old('address', $user->address) }}"></div>
            <div class="form-field"><label for="city">City</label><input id="city" name="city" value="{{ old('city', $user->city) }}"></div>
            <div class="form-field"><label for="country">Country</label><input id="country" name="country" value="{{ old('country', $user->country) }}"></div>
            <div class="form-field"><label for="postal_code">Postal code</label><input id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}"></div>
        </div>
        <div class="form-actions"><button class="button button-primary" type="submit"><i class="fa-solid fa-check"></i> Save changes</button></div>
    </form>
    <div class="form-divider"></div>
    <div id="change-password" class="form-section-title"><span class="form-section-icon form-icon-purple"><i class="fa-solid fa-key"></i></span><div><h2>Change password</h2><p>Choose a strong password you don't use elsewhere</p></div></div>
    <form method="POST" action="{{ route('account.password') }}">@csrf @method('PUT')
        <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2"><div class="form-field"><label for="current_password">Current password <span>*</span></label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required></div><div class="form-field"><label for="new_password">New password <span>*</span></label><input id="new_password" name="password" type="password" autocomplete="new-password" required></div><div class="form-field"><label for="password_confirmation">Confirm new password <span>*</span></label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div></div>
        <div class="form-actions"><button class="button button-primary" type="submit"><i class="fa-solid fa-lock"></i> Update password</button></div>
    </form>
</section>
@endsection
