<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SuperAdmin;
use App\Notifications\LoginOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $key = 'login:'.Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many sign-in attempts. Try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();
        $guard = 'web';
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $user = SuperAdmin::where('email', $credentials['email'])->first();
            $guard = 'superadmin';
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        RateLimiter::clear($key);
        if ($user->status !== 'active') {
            throw ValidationException::withMessages(['email' => 'This account is inactive. Please contact an administrator.']);
        }

        $code = (string) random_int(100000, 999999);
        $this->storeLoginOtp($user, $code);
        $request->session()->put([
            'pending_login_user_id' => $user->id,
            'pending_login_guard' => $guard,
            'pending_login_remember' => $request->boolean('remember'),
        ]);
        Notification::route('mail', $user->email)->notify(new LoginOtpNotification($code));

        return redirect()->route('otp.show')->with('status', 'A verification code has been sent to your email address.');
    }

    public function showOtp(Request $request): View|RedirectResponse
    {
        abort_unless(
            $request->session()->has('pending_login_user_id')
                && in_array($request->session()->get('pending_login_guard', 'web'), ['web', 'superadmin'], true),
            403,
        );

        return view('auth.otp');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $userId = $request->session()->get('pending_login_user_id');
        abort_unless($userId, 403);
        $guard = $request->session()->get('pending_login_guard', 'web');
        abort_unless(in_array($guard, ['web', 'superadmin'], true), 403);
        $key = 'otp:'.$guard.'|'.$userId.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['code' => 'Too many verification attempts. Request a new code later.']);
        }

        $user = $this->findPendingLoginAccount($userId, $guard);
        if ($user->status !== 'active') {
            $user->loginOtp()->delete();
            $request->session()->forget(['pending_login_user_id', 'pending_login_guard', 'pending_login_remember']);

            return redirect()->route('login')->withErrors(['email' => 'This account is inactive. Please contact an administrator.']);
        }

        $otp = $user->loginOtp;
        if (! $otp || $otp->expires_at->isPast() || $otp->attempts >= 5 || ! Hash::check((string) $request->input('code'), $otp->code_hash)) {
            RateLimiter::hit($key, 600);
            if ($otp && $otp->expires_at->isFuture()) {
                $otp->increment('attempts');
            }
            throw ValidationException::withMessages(['code' => 'That code is invalid or expired. Request a new code and try again.']);
        }

        $user->loginOtp()->delete();
        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();
        Auth::guard($guard)->login($user, (bool) $request->session()->get('pending_login_remember', false));
        RateLimiter::clear($key);
        $request->session()->forget(['pending_login_user_id', 'pending_login_guard', 'pending_login_remember']);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('pending_login_user_id');
        abort_unless($userId, 403);
        $guard = $request->session()->get('pending_login_guard', 'web');
        abort_unless(in_array($guard, ['web', 'superadmin'], true), 403);
        $key = 'otp-resend:'.$guard.'|'.$userId.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages(['code' => 'A new code was sent recently. Please wait before requesting another.']);
        }
        RateLimiter::hit($key, 60);
        RateLimiter::clear('otp:'.$guard.'|'.$userId.'|'.$request->ip());
        $user = $this->findPendingLoginAccount($userId, $guard);
        $code = (string) random_int(100000, 999999);
        $this->storeLoginOtp($user, $code);
        Notification::route('mail', $user->email)->notify(new LoginOtpNotification($code));

        return back()->with('status', 'A new verification code has been sent.');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
                Rule::unique(SuperAdmin::class, 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = User::create([...$data, 'role' => 'staff', 'status' => 'active']);
        $code = (string) random_int(100000, 999999);
        $this->storeLoginOtp($user, $code);
        $request->session()->put([
            'pending_login_user_id' => $user->id,
            'pending_login_guard' => 'web',
            'pending_login_remember' => false,
        ]);
        Notification::route('mail', $user->email)->notify(new LoginOtpNotification($code));

        return redirect()->route('otp.show')->with('status', 'A verification code has been sent to your email address.');
    }

    private function findPendingLoginAccount(int $userId, string $guard): User|SuperAdmin
    {
        return $guard === 'superadmin'
            ? SuperAdmin::findOrFail($userId)
            : User::findOrFail($userId);
    }

    private function storeLoginOtp(User|SuperAdmin $user, string $code): void
    {
        $identity = $user instanceof SuperAdmin
            ? ['super_admin_id' => $user->id]
            : ['user_id' => $user->id];

        $user->loginOtp()->updateOrCreate($identity, [
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($request->only('email'));

        return back()->with($status === Password::RESET_LINK_SENT ? 'status' : 'error', __($status));
    }

    public function showResetPassword(Request $request): View
    {
        return view('auth.reset-password', ['token' => $request->route('token'), 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => $password])->setRememberToken(Str::random(60))->save();
            $user->loginOtp()->delete();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        Auth::guard('superadmin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
