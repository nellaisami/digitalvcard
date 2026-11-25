<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="default-avatar" content="{{ asset('images/default-avatar.svg') }}">
    <meta name="default-vcard-template" content="{{ asset('images/default-vcard-template.svg') }}">
    <title>@yield('title', 'Dashboard') · Northstar Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/management.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>if(localStorage.getItem('admin-theme')==='dark')document.documentElement.dataset.theme='dark';</script>
    @stack('head')
</head>
<body>
<div class="app-shell">
    <div class="sidebar-scrim" data-sidebar-close></div>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark"><i class="fa-solid fa-n"></i></span><span>northstar<span class="brand-dot">.</span><small>ADMIN CONSOLE</small></span></a>
        <div class="nav-caption">WORKSPACE</div>
        <nav class="side-nav" aria-label="Main navigation">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="fa-solid fa-grip"></i><span>Dashboard</span></a>
            @if(auth()->user()->role === 'superadmin')
                <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.index') }}"><i class="fa-solid fa-shield-halved"></i><span>Admin</span></a>
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="fa-solid fa-users"></i><span>Users</span></a>
                <a class="nav-link {{ request()->routeIs('plans.*', 'taxes.*') ? 'active' : '' }}" href="{{ route('plans.index') }}"><i class="fa-solid fa-layer-group"></i><span>Plans</span></a>
                <a class="nav-link {{ request()->routeIs('vcard-templates.*') ? 'active' : '' }}" href="{{ route('vcard-templates.index') }}"><i class="fa-regular fa-images"></i><span>vCard Templates</span></a>
            @endif
            <a class="nav-link {{ request()->routeIs('organizations.*') ? 'active' : '' }}" href="{{ route('organizations.index') }}"><i class="fa-regular fa-building"></i><span>Organizations</span></a>
            <a class="nav-link {{ request()->routeIs('vcards.*') ? 'active' : '' }}" href="{{ route('vcards.index') }}"><i class="fa-regular fa-address-card"></i><span>vCards</span></a>
        </nav>
        <div class="nav-caption nav-caption-lower">PREFERENCES</div>
        <nav class="side-nav">
            <a class="nav-link {{ request()->routeIs('account.*') ? 'active' : '' }}" href="{{ route('account.edit') }}"><i class="fa-solid fa-sliders"></i><span>Settings</span></a>
        </nav>
        <div class="sidebar-bottom">
            <div class="help-card"><span class="help-icon"><i class="fa-regular fa-circle-question"></i></span><strong>Need a hand?</strong><p>Visit our help center for tips and answers.</p><a href="mailto:support@example.com">Get support <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
            <div class="sidebar-foot">© {{ date('Y') }} Northstar Inc.</div>
        </div>
    </aside>
    <main class="main-area">
        <header class="topbar">
            <button type="button" class="icon-button mobile-menu" aria-label="Open menu" data-sidebar-open><i class="fa-solid fa-bars"></i></button>
            @if(auth()->user()->role === 'superadmin')
                <form method="GET" action="{{ route('users.index') }}" class="top-search"><i class="fa-solid fa-magnifying-glass"></i><input id="quick-search" name="search" value="{{ request('search') }}" placeholder="Search users..." autocomplete="off"><kbd>⌘ K</kbd></form>
            @else
                <span class="top-workspace-label">Workspace overview</span>
            @endif
            <div class="top-actions">
                <button class="icon-button theme-toggle" type="button" aria-label="Toggle dark mode"><i class="fa-regular fa-moon"></i></button>
                <div class="notification-wrap"><button class="icon-button notification-button" type="button" aria-label="Notifications" data-notification-toggle aria-expanded="false"><i class="fa-regular fa-bell"></i><span></span></button><div class="notification-popover" data-notification-popover><strong>You’re all caught up</strong><small>There are no new notifications.</small></div></div>
                <span class="top-divider"></span>
                <div class="profile-wrap">
                    <button class="profile-button" type="button" data-profile-toggle aria-expanded="false">
                        <img class="avatar avatar-photo" src="{{ auth()->user()->profile_photo_url }}" alt="">
                        <span class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small></span>
                        <i class="fa-solid fa-chevron-down profile-chevron"></i>
                    </button>
                    <div class="profile-menu" data-profile-menu>
                        <a href="{{ route('account.edit') }}"><i class="fa-regular fa-user"></i> Account settings</a>
                        <a href="{{ route('account.edit') }}#change-password"><i class="fa-solid fa-key"></i> Change password</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign out</button></form>
                    </div>
                </div>
            </div>
        </header>
        <div class="content-wrap">
            <div class="page-heading">
                <div><div class="breadcrumbs"><a href="{{ route('dashboard') }}">Workspace</a><i class="fa-solid fa-chevron-right"></i><span>@yield('breadcrumb', 'Overview')</span></div><h1>@yield('heading', 'Dashboard overview')</h1><p>@yield('subheading', 'Here’s what’s happening across your workspace today.')</p></div>
                @yield('heading-action')
            </div>
            @if(session('status'))<div class="alert alert-success" data-flash-toast data-toast-message="{{ session('status') }}" data-toast-type="success"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}<button type="button" data-dismiss>&times;</button></div>@endif
            @if(session('error'))<div class="alert alert-error" data-flash-toast data-toast-message="{{ session('error') }}" data-toast-type="error"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}<button type="button" data-dismiss>&times;</button></div>@endif
            @if($errors->any())<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><div><strong>Please check the form details.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div><button type="button" data-dismiss>&times;</button></div>@endif
            @yield('content')
        </div>
        <footer class="footer"><span>© {{ date('Y') }} Northstar Admin. All rights reserved.</span><span>Made for teams that move forward <i class="fa-solid fa-heart"></i></span></footer>
    </main>
</div>
<script src="{{ asset('js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
