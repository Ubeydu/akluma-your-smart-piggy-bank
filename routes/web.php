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

// Localized route group
Route::prefix('{locale}')
    ->middleware('locale')
    ->where(['locale' => '[a-z]{2}'])
    ->group(function () {

        // Localized welcome route
        Route::get('/', function () {
            return view('welcome');
        })->name('localized.welcome');

        // Localized dashboard route
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->middleware(['auth', 'verified'])
            ->name('localized.dashboard');

        // Add other routes here (without leading slash) and use `localized.` prefix for route names
        Route::get('piggy-banks', [PiggyBankController::class, 'index'])
            ->middleware(['auth', 'verified'])
            ->name('localized.piggy-banks.index');

        Route::get('piggy-banks/{piggy_id}', [PiggyBankController::class, 'show'])
            ->middleware(['auth', 'verified'])
            ->name('localized.piggy-banks.show')
            ->where('piggy_id', '[0-9]+');

        Route::put('piggy-banks/{piggy_id}', [PiggyBankController::class, 'update'])
            ->middleware(['auth', 'verified'])
            ->name('localized.piggy-banks.update')
            ->where('piggy_id', '[0-9]+');

        Route::patch('piggy-banks/{piggy_id}/pause', [ScheduledSavingController::class, 'pausePiggyBank'])
            ->middleware(['auth', 'verified'])
            ->name('localized.piggy-banks.pause')
            ->where('piggy_id', '[0-9]+');

        Route::patch('piggy-banks/{piggy_id}/resume', [ScheduledSavingController::class, 'resumePiggyBank'])
            ->middleware(['auth', 'verified'])
            ->name('localized.piggy-banks.resume')
            ->where('piggy_id', '[0-9]+');

        Route::patch('piggy-banks/{piggy_id}/update-status-cancelled', [PiggyBankController::class, 'updateStatusToCancelled'])
            ->middleware(['auth', 'verified'])
            ->name('localized.piggy-banks.update-status-cancelled')
            ->where('piggy_id', '[0-9]+');

        Route::get('piggy-banks/{piggy_id}/schedule', [ScheduledSavingController::class, 'getSchedulePartial'])
            ->middleware(['auth', 'verified'])
            ->name('localized.piggy-banks.schedule')
            ->where('piggy_id', '[0-9]+');

        Route::post('piggy-banks/{piggy_id}/cancel', [PiggyBankController::class, 'cancel'])
            ->middleware(['auth', 'verified'])
            ->name('localized.piggy-banks.cancel')
            ->where('piggy_id', '[0-9]+');

        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('profile', [ProfileController::class, 'edit'])->name('localized.profile.edit');
            Route::patch('profile', [ProfileController::class, 'update'])->name('localized.profile.update');
            Route::delete('profile', [ProfileController::class, 'destroy'])->name('localized.profile.destroy');

            Route::patch('preferences/update',
                [App\Http\Controllers\UserPreferencesController::class, 'updatePreferences'])
                ->name('localized.preferences.update');

            //    Route::get('/test-piggy-full/{piggyBankId}', [TestController::class, 'testPiggyBank'])
            //        ->name('test.piggy-full');

            Route::post('test-date/{piggy_id}', function ($piggy_id, Request $request) {
                $piggyBank = App\Models\PiggyBank::findOrFail($piggy_id);
                session(['test_date' => $request->test_date]);

                return back()->with('success', 'Test date set to: '.$request->test_date);
            })->name('localized.test.set-date')
                ->where('piggy_id', '[0-9]+');

            Route::post('test-date/{piggy_id}/clear', function () {
                session()->forget('test_date');

                return back()->with('success', 'Test date cleared');
            })->name('localized.test.clear-date')
                ->where('piggy_id', '[0-9]+');

            Route::patch('scheduled-savings/{periodicSaving}', [ScheduledSavingController::class, 'update'])
                ->middleware(['auth', 'verified'])
                ->name('localized.scheduled-savings.update');

        });

        Route::view('terms-of-service', 'legal.terms')->name('localized.terms');
        Route::view('privacy-policy', 'legal.privacy')->name('localized.privacy');

        Route::prefix('create-piggy-bank')
            ->name('localized.create-piggy-bank.')
            ->middleware(['conditional.layout'])
            ->group(function () {

                Route::get('step-1', [PiggyBankCreateController::class, 'step1'])->name('step-1');

                Route::post('clear', function () {
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
                })->name('clear');

                Route::post('api/link-preview', [PiggyBankCreateController::class, 'fetchLinkPreview'])->name('api.link-preview');
                Route::post('cancel', [PiggyBankCreateController::class, 'cancelCreation'])->name('cancel');

                Route::get('step-2', [PiggyBankCreateController::class, 'showStep2'])->name('step-2.get');
                Route::post('step-2', [PiggyBankCreateController::class, 'step2'])->name('step-2');

                Route::post('choose-strategy', [PiggyBankCreateController::class, 'storeStrategySelection'])->name('choose-strategy');

                Route::prefix('pick-date')->name('pick-date.')->group(function () {
                    Route::get('step-3', [PiggyBankCreateController::class, 'renderStrategyView'])->name('step-3');
                    Route::post('calculate-frequencies', [PiggyBankCreateController::class, 'calculateFrequencyOptions'])->name('calculate-frequencies');
                    Route::post('store-frequency', [PiggyBankCreateController::class, 'storeSelectedFrequency'])->name('store-frequency');
                    Route::post('show-summary', [PiggyBankCreateController::class, 'showSummary'])->name('show-summary');
                    Route::get('summary', [PiggyBankCreateController::class, 'showSummary'])->name('summary');
                    Route::post('store', [PiggyBankCreateController::class, 'storePiggyBank'])
                        ->middleware(['auth', 'verified'])
                        ->name('store');
                });

                /**
                 * Returns flash messages as JSON for AJAX requests.
                 * This endpoint is specifically designed to avoid HTML injection and unwanted side effects.
                 */
                Route::get('get-flash-messages', function () {
                    // Get message values
                    $successMessage = session('success');
                    $errorMessage = session('error');
                    $warningMessage = session('warning');
                    $infoMessage = session('info');

                    // Clear the messages after reading them
                    if ($successMessage || $errorMessage || $warningMessage || $infoMessage) {
                        session()->forget(['success', 'error', 'warning', 'info']);
                    }

                    // Return just the data, not HTML that could contain scripts
                    return response()->json([
                        'success' => $successMessage,
                        'error' => $errorMessage,
                        'warning' => $warningMessage,
                        'info' => $infoMessage,
                    ]);
                })->name('get-flash-messages');

                Route::prefix('enter-saving-amount')->name('enter-saving-amount.')->group(function () {
                    Route::get('step-3', [PiggyBankCreateController::class, 'renderStrategyView'])->name('step-3');
                });
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

Route::get('currency/switch/{currency}', function ($currency, Request $request) {
    try {
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
            \Illuminate\Support\Facades\Log::info('Set locale from referer', [
                'new_locale' => app()->getLocale(),
            ]);
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

        return redirect()->back();
    } catch (Exception $e) {
        // If anything goes wrong with session storage or currency retrieval
        \Illuminate\Support\Facades\Log::error('Currency switch error', [
            'error' => $e->getMessage(),
        ]);
        session()->flash('error', __('There was a problem setting currency. Please reload the page and try again.'));

        return redirect()->back();
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

Route::fallback(function () {
    $locale = Auth::check() ? Auth::user()->language : (session('locale') ?? 'en');

    $availableLocales = array_keys(config('app.available_languages', []));
    if (! in_array($locale, $availableLocales)) {
        $locale = 'en';
    }

    return redirect("/$locale");
});

// Replace your old test route with this one
Route::get('/test-route-service', function () {
    echo '<h2>Testing LocalizedRouteService</h2>';

    // Test 1: Check if class exists
    if (class_exists('\App\Services\LocalizedRouteService')) {
        echo '<pre>✅ LocalizedRouteService class exists</pre>';
    } else {
        echo '<pre>❌ LocalizedRouteService class not found</pre>';

        return;
    }

    // Test 2: Check available locales method
    try {
        $locales = \App\Services\LocalizedRouteService::getAvailableLocales();
        echo '<pre>✅ Available locales: '.implode(', ', $locales).'</pre>';
    } catch (Exception $e) {
        echo "<pre>❌ Error getting locales: {$e->getMessage()}</pre>";
    }

    // Test 3: Test a simple route registration
    echo '<h3>Testing Route Registration</h3>';
    try {
        \App\Services\LocalizedRouteService::register(
            'get',
            'dashboard',
            function () {
                return 'Test dashboard';
            },
            'test.dashboard'
        );
        echo '<pre>✅ Route registration completed without errors</pre>';
    } catch (Exception $e) {
        echo "<pre>❌ Route registration error: {$e->getMessage()}</pre>";
    }

    // Test 4: Check if routes were actually created
    echo '<h3>Checking Registered Routes</h3>';
    $routeCollection = Route::getRoutes();
    $testRoutes = [];

    foreach ($routeCollection as $route) {
        if (str_contains($route->getName() ?? '', 'test.dashboard')) {
            $testRoutes[] = $route->uri().' ('.$route->getName().')';
        }
    }

    if (! empty($testRoutes)) {
        echo '<pre>✅ Found registered test routes:</pre>';
        foreach ($testRoutes as $routeInfo) {
            echo "<pre>  - {$routeInfo}</pre>";
        }
    } else {
        echo '<pre>❌ No test routes found</pre>';
    }

    echo '<h3>✅ LocalizedRouteService tests completed!</h3>';
});

require __DIR__.'/auth.php';
