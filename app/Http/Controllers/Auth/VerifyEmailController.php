<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocalizedEmailVerificationRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(LocalizedEmailVerificationRequest $request): RedirectResponse
    {
        //        \Log::debug('🔍 Controller reached - checking signature', [
        //            'has_valid_signature' => $request->hasValidSignature(),
        //            'full_url' => $request->fullUrl(),
        //        ]);

        if ($request->user()->hasVerifiedEmail()) {
            //            \Log::debug('🔍 User already verified - redirecting');
            $redirectUrl = RouteHelper::localizedRoute('localized.dashboard').'?verified=1';

            //            \Log::debug('🔍 Redirect URL', ['url' => $redirectUrl]);
            return redirect($redirectUrl);
        }

        if ($request->user()->markEmailAsVerified()) {
            //            \Log::debug('🔍 Email marked as verified');
            event(new Verified($request->user()));
        }

        //        \Log::debug('🔍 Final redirect');
        $redirectUrl = RouteHelper::localizedRoute('localized.dashboard').'?verified=1';

        //        \Log::debug('🔍 Final redirect URL', ['url' => $redirectUrl]);
        return redirect($redirectUrl);
    }
}
