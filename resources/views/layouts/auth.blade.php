<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') · Northstar Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"><link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body">
<main class="auth-layout">
    <section class="auth-showcase">
        <a class="brand brand-light" href="{{ route('login') }}"><span class="brand-mark"><i class="fa-solid fa-n"></i></span><span>northstar<span class="brand-dot">.</span><small>ADMIN CONSOLE</small></span></a>
        <div class="showcase-content"><span class="eyebrow"><span></span> YOUR BUSINESS, IN FOCUS</span><h1>Clarity to lead.<br><em>Confidence to grow.</em></h1><p>A calmer, smarter way to see your whole business. Everything your team needs, right where you need it.</p><div class="showcase-stats"><div><strong>2.4k<span>+</span></strong><small>active teams</small></div><i></i><div><strong>99.9<span>%</span></strong><small>platform uptime</small></div></div></div>
        <div class="showcase-bottom"><span>Built for better business.</span><span><i class="fa-solid fa-lock"></i> Secure by design</span></div>
        <div class="showcase-orb orb-one"></div><div class="showcase-orb orb-two"></div>
    </section>
    <section class="auth-panel"><div class="auth-card">
        <div class="auth-mobile-brand"><span class="brand-mark"><i class="fa-solid fa-n"></i></span>northstar<span class="brand-dot">.</span></div>
        @yield('content')
        <p class="auth-legal">By continuing, you agree to our <a href="mailto:legal@example.com">Terms of Service</a> and <a href="mailto:privacy@example.com">Privacy Policy</a>.</p>
    </div><div class="auth-copyright">© {{ date('Y') }} Northstar Inc.</div></section>
</main>
<script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>
