<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestLoginCodeRequest;
use App\Http\Requests\Auth\VerifyLoginCodeRequest;
use App\Services\LoginOtpService;
use App\Support\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class LoginCodeController extends Controller
{
    public function __construct(
        protected LoginOtpService $loginOtpService
    ) {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function sendCode(RequestLoginCodeRequest $request): RedirectResponse
    {
        $throttleKey = 'otp-login:request:'.mb_strtolower(trim($request->string('identifier')->toString())).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'identifier' => "Terlalu banyak permintaan OTP. Coba lagi {$seconds} detik lagi.",
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $result = $this->loginOtpService->sendCode($request->string('identifier')->toString());

        return redirect()
            ->route('login.verify.form', [
                'identifier' => $result['identifier'],
                'channel' => $result['channel'],
            ])
            ->with('status', 'Kode OTP sudah dikirim. Silakan cek email Anda.');
    }

    public function showVerifyForm(): View
    {
        return view('auth.login-verify', [
            'identifier' => request()->query('identifier', old('identifier', '')),
            'channel' => request()->query('channel', old('channel', 'email')),
        ]);
    }

    public function verifyCode(VerifyLoginCodeRequest $request): RedirectResponse
    {
        $throttleKey = 'otp-login:verify:'.mb_strtolower(trim($request->string('identifier')->toString())).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'code' => "Terlalu banyak percobaan OTP. Coba lagi {$seconds} detik lagi.",
            ]);
        }

        RateLimiter::hit($throttleKey, 120);

        $user = $this->loginOtpService->verifyCode(
            $request->string('identifier')->toString(),
            $request->string('code')->toString()
        );

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        if (in_array(UserRole::normalize($user->role), UserRole::backofficeValues(), true)) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('home', absolute: false));
    }
}
