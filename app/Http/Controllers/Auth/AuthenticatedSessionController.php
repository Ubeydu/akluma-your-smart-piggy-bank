<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\PiggyBank;
use App\Services\LinkPreviewService;
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

        if (session()->has('pending_classic_piggy_bank')) {
            $data = session()->pull('pending_classic_piggy_bank');
            $preview = ['title' => null, 'description' => null, 'image' => null, 'url' => null];

            if (! empty($data['link'])) {
                try {
                    $preview = app(LinkPreviewService::class)->getPreviewData($data['link']);
                } catch (\Exception $e) {
                    $preview['url'] = $data['link'];
                }
            }

            $piggyBank = PiggyBank::createClassic(auth()->id(), $data, $preview);

            return redirect(localizedRoute('localized.piggy-banks.index'))
                ->with('newPiggyBankId', $piggyBank->id)
                ->with('newPiggyBankCreatedTime', time())
                ->with('success', __('classic_piggy_bank_created_success'))
                ->with('success_duration', 10000);
        }

        return redirect()->intended(localizedRoute('localized.piggy-banks.index'));
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
