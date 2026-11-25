@extends('layouts.auth')
@section('title', 'Create account')
@section('content')
<span class="auth-eyebrow">GET STARTED</span><h2>Create your account</h2><p class="auth-intro">Start with a free account. You can invite your team later.</p>
@if($errors->any())<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('register.store') }}" class="auth-form">@csrf
    <label for="name">Full name</label><div class="input-wrap"><i class="fa-regular fa-user"></i><input id="name" name="name" value="{{ old('name') }}" placeholder="Your name" autocomplete="name" required></div>
    <label for="email">Email address</label><div class="input-wrap"><i class="fa-regular fa-envelope"></i><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required></div>
    <label for="password">Password</label><div class="input-wrap"><i class="fa-solid fa-lock"></i><input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
    <label for="password_confirmation">Confirm password</label><div class="input-wrap"><i class="fa-solid fa-lock"></i><input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat your password" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
    <button class="button button-primary button-full" type="submit">Create account <i class="fa-solid fa-arrow-right"></i></button>
</form>
<div class="auth-divider"><span>OR</span></div><p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
@endsection
