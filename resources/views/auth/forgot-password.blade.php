@extends('layouts.auth')
@section('title', 'Reset password')
@section('content')
<span class="otp-symbol"><i class="fa-solid fa-key"></i></span><span class="auth-eyebrow">ACCOUNT RECOVERY</span><h2>Forgot your password?</h2><p class="auth-intro">Enter the email address linked to your account and we’ll send you a reset link.</p>
@if(session('status'))<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>@endif
@if(session('error'))<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.email') }}" class="auth-form">@csrf
    <label for="email">Email address</label><div class="input-wrap"><i class="fa-regular fa-envelope"></i><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required autofocus></div>
    <button class="button button-primary button-full" type="submit">Send reset link <i class="fa-solid fa-arrow-right"></i></button>
</form><p class="auth-switch"><a href="{{ route('login') }}">Back to sign in</a></p>
@endsection
