@extends('layouts.auth')
@section('title', 'Verify sign-in')
@section('content')
<span class="otp-symbol"><i class="fa-regular fa-envelope"></i></span><span class="auth-eyebrow">ONE MORE STEP</span><h2>Check your inbox</h2><p class="auth-intro">We sent a 6-digit verification code to your email address. It’s valid for 10 minutes.</p>
@if(session('status'))<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('otp.verify') }}" class="auth-form">@csrf
    <label for="code">Verification code</label><div class="input-wrap"><i class="fa-solid fa-hashtag"></i><input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" autocomplete="one-time-code" class="otp-input" required autofocus></div>
    <button class="button button-primary button-full" type="submit">Verify and sign in <i class="fa-solid fa-arrow-right"></i></button>
</form>
<p class="auth-switch">Didn’t receive a code? <form method="POST" action="{{ route('otp.resend') }}" class="inline-form">@csrf<button class="text-button">Send a new code</button></form></p>
<p class="auth-switch"><a href="{{ route('login') }}">Back to sign in</a></p>
@endsection
