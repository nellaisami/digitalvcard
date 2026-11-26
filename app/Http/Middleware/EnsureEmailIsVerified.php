<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->email_verified_at === null) {
            Auth::guard('web')->logout();
            Auth::guard('superadmin')->logout();
            $request->session()->forget([
                'pending_login_user_id',
                'pending_login_guard',
                'pending_login_remember',
            ]);
            $request->session()->regenerate();

            return redirect()->route('login')->withErrors([
                'email' => 'Verify your email address with the one-time code before continuing.',
            ]);
        }

        return $next($request);
    }
}
