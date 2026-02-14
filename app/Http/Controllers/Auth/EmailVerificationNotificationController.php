<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateUnverifiedEmailRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('localized.dashboard', ['locale' => app()->getLocale()], absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Update the email address for an unverified user and resend verification.
     */
    public function updateEmail(UpdateUnverifiedEmailRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request): void {
            $user->email = $request->validated('email');
            $user->email_verified_at = null;
            $user->save();

            $user->sendEmailVerificationNotification();
        });

        return back()->with('status', 'verification-link-sent');
    }
}
