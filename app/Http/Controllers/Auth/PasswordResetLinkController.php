<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Cooldown period in seconds between password reset emails.
     */
    private const RESET_COOLDOWN_SECONDS = 120;

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $throttleKey = 'password-reset:'.Str::lower($request->string('email'));

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email'))
                ->with('cooldown', $seconds);
        }

        RateLimiter::hit($throttleKey, self::RESET_COOLDOWN_SECONDS);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('email'))
                ->with('cooldown', self::RESET_COOLDOWN_SECONDS);
        }

        return $status == Password::RESET_LINK_SENT
            ? back()->withInput($request->only('email'))->with([
                'status' => __($status),
                'cooldown' => self::RESET_COOLDOWN_SECONDS,
            ])
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
