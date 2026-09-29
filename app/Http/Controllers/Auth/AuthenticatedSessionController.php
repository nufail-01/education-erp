<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        $pageConfigs = ['myLayout' => 'blank'];

        return view('content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isActive()) {
            $this->forceLogout($request);
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'Your account is inactive. Please contact your administrator.',
            ]);
        }

        if ($user->institute_id !== null && ! optional($user->institute)->isActive()) {
            $this->forceLogout($request);
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'Your institute is inactive. Please contact the administrator.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Session fixation se bachne ke liye
        $request->session()->regenerate();

        return redirect()->intended(route('pages-home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->forceLogout($request);

        return redirect()->route('login');
    }

    private function forceLogout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}