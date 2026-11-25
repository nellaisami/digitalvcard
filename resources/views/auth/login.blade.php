@extends('layouts.auth')
@section('title', 'Sign in')
@section('content')
<span class="auth-eyebrow">WELCOME BACK</span><h2>Sign in to your account</h2><p class="auth-intro">Enter your details below to access your workspace.</p>
@if(session('status'))<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('login.store') }}" class="auth-form">@csrf
    <label for="email">Email address</label><div class="input-wrap"><i class="fa-regular fa-envelope"></i><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required autofocus></div>
    <div class="label-row"><label for="password">Password</label><a href="{{ route('password.request') }}">Forgot password?</a></div>
    <div class="input-wrap"><i class="fa-solid fa-lock"></i><input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required><button type="button" class="password-toggle" data-password-toggle aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
    <label class="check-row"><input type="checkbox" name="remember" value="1"><span class="checkmark"></span>Remember me <span class="muted">(30 days)</span></label>
    <button class="button button-primary button-full" type="submit">Continue <i class="fa-solid fa-arrow-right"></i></button>
</form>
<div class="auth-divider"><span>OR</span></div><p class="auth-switch">New to Northstar? <a href="{{ route('register') }}">Create an account</a></p>
<div class="secure-note"><i class="fa-solid fa-shield-halved"></i><span><strong>Your account is protected</strong><small>We’ll email you a one-time code each time you sign in.</small></span></div>
@endsection
