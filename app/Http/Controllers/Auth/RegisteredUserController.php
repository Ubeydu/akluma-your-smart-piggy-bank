<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use App\Models\PiggyBank;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'timezone' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
            'terms' => ['accepted'],
            'privacy' => ['accepted'],
        ]);

        // Get current locale from session or default
        $currentLocale = session('locale', app()->getLocale());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'timezone' => $request->timezone ?? 'UTC',
            'language' => $currentLocale,
            'accepted_terms_at' => $request->boolean('terms') ? now() : null,
            'accepted_privacy_at' => $request->boolean('privacy') ? now() : null,
        ]);

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('Failed to send verification email during registration', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        Auth::login($user);

        $piggyBank = PiggyBank::createClassicFromSession($user->id);

        if ($piggyBank) {
            return redirect(RouteHelper::localizedRoute('localized.piggy-banks.index'))
                ->with('newPiggyBankId', $piggyBank->id)
                ->with('newPiggyBankCreatedTime', time())
                ->with('success', __('classic_piggy_bank_created_success'))
                ->with('success_duration', 10000);
        }

        return redirect(RouteHelper::localizedRoute('localized.dashboard'));
    }
}
