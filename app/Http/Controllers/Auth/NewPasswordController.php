<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        //        \Log::debug('🔍 NewPasswordController store() reached!', [
        //            'email' => $request->email,
        //            'token_provided' => $request->token ? 'yes' : 'no',
        //        ]);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        //        \Log::debug('🔍 About to call Password::reset');

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                //                \Log::debug('🔍 Inside password reset callback', [
                //                    'user_email' => $user->email,
                //                    'old_password_hash' => substr($user->password, 0, 20).'...',
                //                ]);

                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                //                \Log::debug('🔍 Password saved to database', [
                //                    'new_password_hash' => substr($user->password, 0, 20).'...',
                //                ]);

                event(new PasswordReset($user));
            }
        );

        //        \Log::debug('🔍 Password reset status', ['status' => $status]);

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('localized.login.'.app()->getLocale(), ['locale' => app()->getLocale()])->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
