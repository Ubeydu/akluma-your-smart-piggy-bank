<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PiggyBankController;
use App\Http\Controllers\PiggyBankCreateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduledSavingController;
use App\Http\Controllers\UserPreferencesController;
use App\Models\User;
use Brick\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

// Redirect base URL to localized version
Route::get('/', function () {
    // Get user's preferred locale or default to English
    $locale = Auth::check() ? Auth::user()->language : (session('locale') ?? 'en');

    // Validate against available languages
    $availableLocales = array_keys(config('app.available_languages', []));
    if (! in_array($locale, $availableLocales)) {
        $locale = 'en';
    }

    if (! request()->is($locale) && ! request()->is($locale.'/*')) {
        return redirect("/$locale");
    }

    abort(404); // prevent infinite loop
});

Route::localizedGet('dashboard', [DashboardController::class, 'index'])
    ->name('localized.dashboard')
    ->middleware(['locale', 'auth', 'verified']);

Route::localizedGet('piggy-banks', [PiggyBankController::class, 'index'])
    ->name('localized.piggy-banks.index')
    ->middleware(['auth', 'verified']);

Route::localizedGet('piggy-banks/{piggy_id}', [PiggyBankController::class, 'show'])
    ->name('localized.piggy-banks.show')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedPut('piggy-banks/{piggy_id}', [PiggyBankController::class, 'update'])
    ->name('localized.piggy-banks.update')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedGet('piggy-banks/{piggy_id}/financial-summary', [PiggyBankController::class, 'getFinancialSummary'])
    ->name('localized.piggy-banks.financial-summary')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedPatch('piggy-banks/{piggy_id}/pause', [ScheduledSavingController::class, 'pausePiggyBank'])
    ->name('localized.piggy-banks.pause')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedPatch('piggy-banks/{piggy_id}/resume', [ScheduledSavingController::class, 'resumePiggyBank'])
    ->name('localized.piggy-banks.resume')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedPatch('piggy-banks/{piggy_id}/update-status-cancelled', [PiggyBankController::class, 'updateStatusToCancelled'])
    ->name('localized.piggy-banks.update-status-cancelled')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedGet('piggy-banks/{piggy_id}/schedule', [ScheduledSavingController::class, 'getSchedulePartial'])
    ->name('localized.piggy-banks.schedule')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedPost('piggy-banks/{piggy_id}/cancel', [PiggyBankController::class, 'cancel'])
    ->name('localized.piggy-banks.cancel')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedPost('piggy-banks/{piggy_id}/add-remove-money', [PiggyBankController::class, 'addOrRemoveMoney'])
    ->name('localized.piggy-banks.add-remove-money')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

// Test routes for development
Route::localizedPost('test-date/{piggy_id}', function ($piggy_id, Request $request) {
    // Verify the piggy bank exists but don't store the result
    App\Models\PiggyBank::findOrFail($piggy_id);
    session(['test_date' => $request->test_date]);

    return back()->with('success', 'Test date set to: '.$request->test_date);
})
    ->name('localized.test.set-date')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedPost('test-date/{piggy_id}/clear', function () {
    session()->forget('test_date');

    return back()->with('success', 'Test date cleared');
})
    ->name('localized.test.clear-date')
    ->middleware(['auth', 'verified'])
    ->where('piggy_id', '[0-9]+');

Route::localizedGet('profile', [ProfileController::class, 'edit'])
    ->name('localized.profile.edit')
    ->middleware(['locale', 'auth', 'verified']);

Route::localizedPatch('profile', [ProfileController::class, 'update'])
    ->name('localized.profile.update')
    ->middleware(['auth', 'verified']);

Route::localizedDelete('profile', [ProfileController::class, 'destroy'])
    ->name('localized.profile.destroy')
    ->middleware(['auth', 'verified']);

// Terms and Privacy routes (converted to localized macros)
Route::localizedGet('terms-of-service', function () {
    return view('legal.terms');
})
    ->name('localized.terms');

Route::localizedGet('privacy-policy', function () {
    return view('legal.privacy');
})
    ->name('localized.privacy');

// The welcome route - handled manually to avoid URI conflicts
Route::get('{locale}', function () {
    return view('welcome');
})
    ->name('localized.welcome')
    ->where('locale', implode('|', array_keys(config('app.available_languages'))))
    ->middleware('locale');

// \Log::info('DEBUG: Available locales from config:', array_keys(config('app.available_languages', [])));
// \Log::info('DEBUG: Current locale during route registration:', (array) app()->getLocale());

Route::localizedGet('create-piggy-bank/step-1', [PiggyBankCreateController::class, 'step1'])
    ->name('localized.create-piggy-bank.step-1')
    ->middleware(['conditional.layout']);

Route::localizedGet('create-piggy-bank/step-2', [PiggyBankCreateController::class, 'showStep2'])
    ->name('localized.create-piggy-bank.step-2.get')
    ->middleware(['conditional.layout']);

Route::localizedPost('create-piggy-bank/step-2', [PiggyBankCreateController::class, 'step2'])
    ->name('localized.create-piggy-bank.step-2')
    ->middleware(['conditional.layout']);

Route::localizedPost('create-piggy-bank/cancel', [PiggyBankCreateController::class, 'cancelCreation'])
    ->name('localized.create-piggy-bank.cancel')
    ->middleware(['conditional.layout']);

Route::localizedPost('create-piggy-bank/clear', function () {
    try {
        session()->forget([
            'pick_date_step1.name',
            'pick_date_step1.price',
            'pick_date_step1.link',
            'pick_date_step1.details',
            'pick_date_step1.starting_amount',
            'pick_date_step1.preview',
            'pick_date_step1.currency',
            'pick_date_step3.date',
            'pick_date_step3.calculations',
        ]);
        session()->flash('success', __('You cleared the form.'));
    } catch (Exception) {
        session()->flash('error', __('There was an error during clearing the form. Refresh the page and try again.'));
    }

    return redirect()->back();
})
    ->name('localized.create-piggy-bank.clear')
    ->middleware(['conditional.layout']);

Route::localizedPost('create-piggy-bank/choose-strategy', [PiggyBankCreateController::class, 'storeStrategySelection'])
    ->name('localized.create-piggy-bank.choose-strategy')
    ->middleware(['conditional.layout']);

Route::localizedGet('create-piggy-bank/pick-date/step-3', [PiggyBankCreateController::class, 'renderStrategyView'])
    ->name('localized.create-piggy-bank.pick-date.step-3')
    ->middleware(['conditional.layout']);

Route::localizedGet('create-piggy-bank/enter-saving-amount/step-3', [PiggyBankCreateController::class, 'renderStrategyView'])
    ->name('localized.create-piggy-bank.enter-saving-amount.step-3')
    ->middleware(['conditional.layout']);

Route::localizedGet('create-piggy-bank/pick-date/show-summary', [PiggyBankCreateController::class, 'showSummary'])
    ->name('localized.create-piggy-bank.pick-date.show-summary')
    ->middleware(['conditional.layout']);

Route::localizedPost('create-piggy-bank/pick-date/show-summary', [PiggyBankCreateController::class, 'showSummary'])
    ->name('localized.create-piggy-bank.pick-date.show-summary.post')
    ->middleware(['conditional.layout']);

Route::localizedPost('create-piggy-bank/pick-date/store', [PiggyBankCreateController::class, 'storePiggyBank'])
    ->name('localized.create-piggy-bank.pick-date.store')
    ->middleware(['conditional.layout', 'auth', 'verified']);

// API and internal routes for create-piggy-bank (non-localized)
Route::post('api/create-piggy-bank/link-preview', [PiggyBankCreateController::class, 'fetchLinkPreview'])
    ->name('create-piggy-bank.api.link-preview');

Route::get('{locale}/create-piggy-bank/get-flash-messages', function () {
    $successMessage = session('success');
    $errorMessage = session('error');
    $warningMessage = session('warning');
    $infoMessage = session('info');

    if ($successMessage || $errorMessage || $warningMessage || $infoMessage) {
        session()->forget(['success', 'error', 'warning', 'info']);
    }

    return response()->json([
        'success' => $successMessage,
        'error' => $errorMessage,
        'warning' => $warningMessage,
        'info' => $infoMessage,
    ]);
})->name('create-piggy-bank.get-flash-messages');

Route::post('{locale}/create-piggy-bank/pick-date/calculate-frequencies', [PiggyBankCreateController::class, 'calculateFrequencyOptions'])
    ->name('create-piggy-bank.pick-date.calculate-frequencies')
    ->middleware('locale');

Route::post('create-piggy-bank/pick-date/store-frequency', [PiggyBankCreateController::class, 'storeSelectedFrequency'])
    ->name('create-piggy-bank.pick-date.store-frequency');

Route::post('create-piggy-bank/pick-date/show-summary', [PiggyBankCreateController::class, 'showSummary'])
    ->name('create-piggy-bank.pick-date.show-summary');

Route::post('create-piggy-bank/pick-date/store', [PiggyBankCreateController::class, 'storePiggyBank'])
    ->middleware(['auth', 'verified'])
    ->name('create-piggy-bank.pick-date.store');

// Localized route group
Route::prefix('{locale}')
    ->middleware('locale')
    ->where(['locale' => '[a-z]{2}'])
    ->group(function () {

        Route::middleware(['auth', 'verified'])->group(function () {

            Route::patch('preferences/update',
                [App\Http\Controllers\UserPreferencesController::class, 'updatePreferences'])
                ->name('localized.preferences.update');

            Route::patch('scheduled-savings/{periodicSaving}', [ScheduledSavingController::class, 'update'])
                ->middleware(['auth', 'verified'])
                ->name('localized.scheduled-savings.update');

        });

        Route::post('update-timezone', [UserPreferencesController::class, 'updateTimezone'])
            ->name('localized.update-timezone')
            ->middleware(['auth', 'verified']);

        Route::get('format-date', function (Request $request) {
            $date = $request->query('date');

            if (! $date) {
                return response()->json(['error' => 'Invalid date'], 400);
            }

            try {
                $dateObject = new DateTime($date);

                $locale = app()->getLocale();
                $formatter = new IntlDateFormatter(
                    $locale,
                    IntlDateFormatter::LONG,
                    IntlDateFormatter::NONE
                );
                $formattedDate = $formatter->format($dateObject);

                return response()->json(['formatted_date' => $formattedDate]);
            } catch (Exception $e) {
                return response()->json(['error' => 'Date formatting failed: '.$e->getMessage()], 500);
            }
        })->name('localized.format-date');

    });

Route::get('language/{locale}', function ($locale, Request $request) {
    $availableLocales = array_keys(config('app.available_languages', []));

    if (in_array($locale, $availableLocales)) {
        // Store in session
        Session::put('locale', $locale);

        // Persist in DB for authenticated users
        if (Auth::check()) {
            Auth::user()->update(['language' => $locale]);
        }

        // If this is an AJAX request, just return JSON
        if ($request->ajax()) {
            return response()->json(['success' => true, 'locale' => $locale]);
        }

        // For normal (non-AJAX) requests, proceed with redirect
        $segments = $request->segments();

        if (count($segments) > 0 && in_array($segments[0], $availableLocales)) {
            $segments[0] = $locale; // Replace existing locale
        } else {
            array_unshift($segments, $locale); // Add locale if missing
        }

        $redirectUrl = '/'.implode('/', $segments);

        // Append query string if exists
        if ($request->getQueryString()) {
            $redirectUrl .= '?'.$request->getQueryString();
        }

        return redirect($redirectUrl);
    }

    return redirect()->back();
})->name('global.language.switch');

Route::get('currency/switch/{currency}', action: function ($currency, Request $request) {
    try {
        //        \Illuminate\Support\Facades\Log::info('Currency switch route hit', [
        //            'referer_header' => $request->headers->get('referer'),
        //            'request_path' => $request->path(),
        //            'full_url' => $request->fullUrl(),
        //            'previous_url' => url()->previous(),
        //        ]);

        // Debug locale information
        $referer = $request->headers->get('referer');
        $localeFromReferer = null;

        if ($referer) {
            // Extract locale from referer URL - look for /xx/ pattern
            preg_match('/\/([a-z]{2})\//', $referer, $matches);
            $localeFromReferer = $matches[1] ?? null;
        }

        // Try to set locale based on referer first
        if ($localeFromReferer) {
            app()->setLocale($localeFromReferer);
            //            \Illuminate\Support\Facades\Log::info('Set locale from referer', [
            //                'new_locale' => app()->getLocale(),
            //            ]);
        }

        // Validate currency exists in config
        if (! isset(config('app.currencies')[$currency])) {
            throw new Exception('Invalid currency.');
        }

        // Store the currency in session
        session(['currency' => $currency]);

        // Store in user record if authenticated
        if (auth()->check()) {
            /** @var User $user */
            $user = auth()->user();
            $user->setAttribute('currency', $currency);
            $user->save();
        }

        // Get the translated name with debug
        $currencyName = __(config('app.currencies')[$currency]['name']);

        $successMessage = __('You switched the currency to :currency', ['currency' => $currencyName]);

        session()->flash('success', $successMessage);

        // Determine redirect target without triggering route() if avoidable
        $redirectTarget = $referer;

        if (! $redirectTarget) {
            try {
                $redirectTarget = route('localized.dashboard', ['locale' => app()->getLocale()]);
            } catch (Exception $e) {
                \Log::warning('Dashboard route not found, falling back to root', ['locale' => app()->getLocale()]);
                $redirectTarget = '/';
            }
        }

        //        \Illuminate\Support\Facades\Log::info('Currency switch: redirect decision', [
        //            'referer_header' => $request->headers->get('referer'),
        //            'referer_variable' => $referer,
        //            'redirect_target' => $redirectTarget,
        //        ]);


        //        \Illuminate\Support\Facades\Log::info('Redirect response created', [
        //            'response_class' => get_class($response),
        //            'response_content' => method_exists($response, 'getTargetUrl') ? $response->getTargetUrl() : 'unknown',
        //        ]);

        return redirect($redirectTarget);
    } catch (Exception $e) {
        \Illuminate\Support\Facades\Log::error('Currency switch error', [
            'error' => $e->getMessage(),
            'referer' => $request->headers->get('referer'),
            'previous_url' => url()->previous(),
        ]);

        session()->flash('error', __('There was a problem setting currency. Please reload the page and try again.'));

        // Try route fallback safely
        $fallback = url()->previous();
        if (! $fallback) {
            try {
                $fallback = route('localized.dashboard', ['locale' => app()->getLocale()]);
            } catch (Exception $ex) {
                \Log::warning('Dashboard route not found in catch block, falling back to root', ['locale' => app()->getLocale()]);
                $fallback = '/';
            }
        }

        \Illuminate\Support\Facades\Log::info('Redirecting after error to', ['url' => $fallback]);

        return redirect($fallback);
    }
})->name('global.currency.switch');

// Debug route for currency
// visit this to test: http://127.0.0.1:8000/current-currency
Route::get('/current-currency', function () {
    $currencyCode = session('currency', config('app.default_currency'));
    $currencyInfo = config('app.currencies')[$currencyCode];

    dd([
        'code' => $currencyCode,
        'name' => $currencyInfo['name'],
        'decimal_places' => $currencyInfo['decimal_places'],
    ]);
});

if (app()->environment('local')) {
    Route::get('/test-money', function () {
        try {
            // Test XOF with decimal
            $xof_decimal = Money::of('1000.00', 'XOF');
            dump('XOF with decimal: '.$xof_decimal->getAmount());
            dump('XOF with decimal (minor units): '.$xof_decimal->getMinorAmount()->toInt());

            // Test XOF without decimal
            $xof_whole = Money::of('1000', 'XOF');
            dump('XOF without decimal: '.$xof_whole->getAmount());
            dump('XOF without decimal (minor units): '.$xof_whole->getMinorAmount()->toInt());

            // Compare with EUR for reference
            $eur = Money::of('1000.00', 'EUR');
            dump('EUR: '.$eur->getAmount());
            dump('EUR (minor units): '.$eur->getMinorAmount()->toInt());

        } catch (Exception $e) {
            dump('Error: '.$e->getMessage());
        }
    });
}

if (app()->environment('local')) {
    Route::get('/test-currency-helper', function () {
        // Test with XOF (should return false)
        dump('XOF has decimals: '.(App\Helpers\CurrencyHelper::hasDecimalPlaces('XOF') ? 'true' : 'false'));

        // Test with EUR (should return true)
        dump('EUR has decimals: '.(App\Helpers\CurrencyHelper::hasDecimalPlaces('EUR') ? 'true' : 'false'));

        // Test with your current session currency
        $currentCurrency = session('currency', config('app.default_currency'));
        dump('Current currency ('.$currentCurrency.') has decimals: '.
            (App\Helpers\CurrencyHelper::hasDecimalPlaces($currentCurrency) ? 'true' : 'false'));
    });
}

if (app()->environment('local')) {
    Route::get('/email/preview/saving-reminder', function () {
        $user = App\Models\User::first();
        $piggyBank = $user->piggyBanks()->first();
        $scheduledSaving = $piggyBank->scheduledSavings()->first();

        return new App\Mail\SavingReminderMail(
            $user,
            $piggyBank,
            $scheduledSaving
        );
    });
}

Route::get('/debug-route-registration', function () {
    echo '<h1>🔍 Route Registration Debug</h1>';

    // Clear existing routes to start fresh
    echo '<h2>Clearing existing routes...</h2>';

    // Re-register just the welcome route to see what happens
    echo '<h2>Manually testing welcome route registration...</h2>';

    // Test the macro directly
    try {
        $macro = Route::localizedGet('test-welcome', function () {
            return 'test welcome';
        })->name('localized.test-welcome');

        // Force destructor
        unset($macro);

        echo '<pre>Welcome macro test completed</pre>';

        // Check what routes were created
        $routeCollection = Route::getRoutes();
        foreach ($routeCollection as $route) {
            $name = $route->getName() ?? 'unnamed';
            if (str_contains($name, 'test-welcome')) {
                echo "<pre>✅ Found: $name → {$route->uri()}</pre>";
            }
        }

    } catch (Exception $e) {
        echo "<pre>❌ Error: {$e->getMessage()}</pre>";
    }

    return '';
});

Route::fallback(function (Request $request) {
    // If this fallback is hit because of an auth failure, redirect to log in
    //    \Log::info('🔍 Fallback route hit', [
    //        'path' => $request->path(),
    //        'has_intended_url' => $request->session()->has('url.intended'),
    //        'intended_url' => session('url.intended'),
    //        'requested_locale' => session('requested_locale'),
    //        'segment_1' => $request->segment(1),
    //    ]);

    if ($request->session()->has('url.intended')) {
        // Use the requested locale if available
        $redirectLocale = session('requested_locale', $request->segment(1));
        $availableLocales = array_keys(config('app.available_languages', []));

        if (! in_array($redirectLocale, $availableLocales)) {
            $redirectLocale = session('locale', 'en');
        }

        return redirect()->route('localized.login.'.$redirectLocale, ['locale' => $redirectLocale]);
    }

    // Otherwise, use the existing fallback logic
    $locale = Auth::check() ? Auth::user()->language : (session('locale') ?? 'en');

    $availableLocales = array_keys(config('app.available_languages', []));
    if (! in_array($locale, $availableLocales)) {
        $locale = 'en';
    }

    return redirect("/$locale");
});

require __DIR__.'/auth.php';
