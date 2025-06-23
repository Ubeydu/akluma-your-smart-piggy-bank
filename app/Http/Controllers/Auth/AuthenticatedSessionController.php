<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        //        \Log::info('🔍 Login process - Before authentication', [
        //            'intended_url_before' => session('url.intended'),
        //            'session_id' => session()->getId(),
        //        ]);

        $request->authenticate();

        //        \Log::info('🔍 Login process - After authentication, before session regenerate', [
        //            'intended_url_after_auth' => session('url.intended'),
        //            'session_id' => session()->getId(),
        //        ]);

        $request->session()->regenerate();

        //        \Log::info('🔍 Login process - After session regenerate', [
        //            'intended_url_after_regenerate' => session('url.intended'),
        //            'session_id' => session()->getId(),
        //        ]);

        // Get the intended URL before we call redirect()->intended()
        $intendedUrl = session('url.intended');

        // Create the redirect response
        $response = redirect()->intended(localizedRoute('localized.piggy-banks.index'));

        //        \Log::info('🔍 Login successful - Detailed redirect info', [
        //            'intended_url_before_redirect' => $intendedUrl,
        //            'intended_url_after_redirect' => session('url.intended'),
        //            'redirect_to' => $response->getTargetUrl(),
        //            'session_id' => session()->getId(),
        //        ]);

        return $response;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/'.app()->getLocale());
    }
}
