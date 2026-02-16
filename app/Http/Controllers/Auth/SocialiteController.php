<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(): RedirectResponse
    {
        session(['google_timezone' => request('timezone')]);
        session(['google_language' => request('language')]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google after authentication.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth callback failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect(RouteHelper::localizedRoute('localized.login'))
                ->with('error', __('Google sign-in failed. Please try again.'));
        }

        if (! $googleUser->getEmail() || ! $googleUser->getId()) {
            Log::warning('Google OAuth returned incomplete user data');

            return redirect(RouteHelper::localizedRoute('localized.login'))
                ->with('error', __('Google sign-in failed. Please try again.'));
        }

        $existingUser = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            return $this->handleExistingUser($existingUser, $googleUser);
        }

        return $this->handleNewUser($googleUser);
    }

    /**
     * Log in an existing user who signed in with Google.
     */
    private function handleExistingUser(User $user, \Laravel\Socialite\Two\User $googleUser): RedirectResponse
    {
        if (! $user->google_id) {
            $user->google_id = $googleUser->getId();
        }

        if ($user->email !== $googleUser->getEmail()) {
            $user->email = $googleUser->getEmail();
            $user->email_verified_at = now();
        }

        if (! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
        }

        $user->save();

        Auth::login($user, remember: true);

        return redirect(RouteHelper::localizedRoute('localized.dashboard'));
    }

    /**
     * Create a new user from Google OAuth data and log them in.
     */
    private function handleNewUser(\Laravel\Socialite\Two\User $googleUser): RedirectResponse
    {
        $locale = session('google_language', session('locale', app()->getLocale()));
        $timezone = session('google_timezone', 'UTC');

        $user = User::forceCreate([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'password' => null,
            'google_id' => $googleUser->getId(),
            'email_verified_at' => now(),
            'timezone' => $timezone ?: 'UTC',
            'language' => $locale,
            'accepted_terms_at' => now(),
            'accepted_privacy_at' => now(),
        ]);

        Auth::login($user, remember: true);

        return redirect(RouteHelper::localizedRoute('localized.dashboard'));
    }
}
