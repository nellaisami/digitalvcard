@extends('layouts.auth')
@section('title', 'Choose a new password')
@section('content')
<span class="auth-eyebrow">ACCOUNT RECOVERY</span><h2>Choose a new password</h2><p class="auth-intro">Use at least 8 characters to keep your account secure.</p>
@if($errors->any())<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.update') }}" class="auth-form">@csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <label for="email">Email address</label><div class="input-wrap"><i class="fa-regular fa-envelope"></i><input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required></div>
    <label for="password">New password</label><div class="input-wrap"><i class="fa-solid fa-lock"></i><input id="password" name="password" type="password" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
    <label for="password_confirmation">Confirm new password</label><div class="input-wrap"><i class="fa-solid fa-lock"></i><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
    <button class="button button-primary button-full" type="submit">Save new password <i class="fa-solid fa-arrow-right"></i></button>
</form>
@endsection
