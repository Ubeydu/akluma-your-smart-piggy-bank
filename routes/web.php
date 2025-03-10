<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PiggyBankCreateController;
use App\Http\Controllers\PiggyBankController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduledSavingController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserPreferencesController;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/piggy-banks', [PiggyBankController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('piggy-banks.index');

Route::get('/piggy-banks/{piggyBank}', [PiggyBankController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('piggy-banks.show');

Route::put('/piggy-banks/{piggyBank}', [PiggyBankController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('piggy-banks.update');


Route::patch('/piggy-banks/{piggyBank}/pause', [\App\Http\Controllers\ScheduledSavingController::class, 'pausePiggyBank'])
    ->middleware(['auth', 'verified'])
    ->name('piggy-banks.pause');

Route::patch('/piggy-banks/{piggyBank}/resume', [\App\Http\Controllers\ScheduledSavingController::class, 'resumePiggyBank'])
    ->middleware(['auth', 'verified'])
    ->name('piggy-banks.resume');

Route::patch('/piggy-banks/{piggyBank}/update-status-cancelled', [PiggyBankController::class, 'updateStatusToCancelled'])
    ->middleware(['auth', 'verified'])
    ->name('piggy-banks.update-status-cancelled');

Route::get('/piggy-banks/{piggyBank}/schedule', [ScheduledSavingController::class, 'getSchedulePartial'])
    ->middleware(['auth', 'verified'])
    ->name('piggy-banks.schedule');



Route::post('/piggy-banks/{piggyBank}/cancel', [PiggyBankController::class, 'cancel'])
    ->middleware(['auth', 'verified'])
    ->name('piggy-banks.cancel');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::patch('/preferences/update', [App\Http\Controllers\UserPreferencesController::class, 'updatePreferences'])
        ->name('preferences.update');

//    Route::get('/test-piggy-full/{piggyBankId}', [TestController::class, 'testPiggyBank'])
//        ->name('test.piggy-full');


    Route::post('/test-date/{piggyBank}', function(App\Models\PiggyBank $piggyBank, Request $request) {
        session(['test_date' => $request->test_date]);
        return back()->with('success', 'Test date set to: ' . $request->test_date);
    })->name('test.set-date');

    Route::post('/test-date/{piggyBank}/clear', function(App\Models\PiggyBank $piggyBank) {
        session()->forget('test_date');
        return back()->with('success', 'Test date cleared');
    })->name('test.clear-date');

});

Route::get('/language/{locale}', function ($locale, Request $request) {
    $availableLocales = array_values(config('app.available_languages', []));

    if (in_array($locale, $availableLocales)) {
        // Store in session
        Session::put('locale', $locale);

        // Persist in DB for authenticated users
        if (Auth::check()) {
            Auth::user()->update(['language' => $locale]);
        }
    }

    return redirect()->back();
})->name('language.switch');

//Route::get('/current-locale', function () {
//    dd(app()->getLocale());
//});

Route::get('currency/switch/{currency}', function ($currency) {
    try {
        // Validate currency exists in config
        if (!isset(config('app.currencies')[$currency])) {
            throw new \Exception('Invalid currency.');
        }

        // Store the currency in session
        session(['currency' => $currency]);

        // Store in user record if authenticated
        if (auth()->check()) {
            $user = auth()->user();
            $user->currency = $currency;
            $user->save();
        }

        // Get the translated name
        $currencyName = __(config('app.currencies')[$currency]['name']);

        session()->flash('success', __('You switched the currency to :currency', ['currency' => $currencyName]));

        return redirect()->back();
    } catch (\Exception $e) {
        // If anything goes wrong with session storage or currency retrieval
        session()->flash('error', __('There was a problem setting currency. Please reload the page and try again.'));

        return redirect()->back();
    }
})->name('currency.switch');

// Debug route for currency
// visit this to test: http://127.0.0.1:8000/current-currency
Route::get('/current-currency', function () {
    $currencyCode = session('currency', config('app.default_currency'));
    $currencyInfo = config('app.currencies')[$currencyCode];

    dd([
        'code' => $currencyCode,
        'name' => $currencyInfo['name'],
        'decimal_places' => $currencyInfo['decimal_places']
    ]);
});


// Create New Piggy Bank routes
Route::prefix('create-piggy-bank')
    ->name('create-piggy-bank.')
    ->middleware(['conditional.layout'])
    ->group(function () {
    Route::get('/step-1', [PiggyBankCreateController::class, 'step1'])->name('step-1');

    Route::post('/clear', function() {

//        // Simulate a random error 50% of the time for testing . Uncomment this to test the error message for translations.
//        if (rand(0, 1) === 1) {
//            session()->flash('error', __('There was an error during clearing the form. Refresh the page and try again.'));
//            return redirect()->back();
//        }

        try {
        // Clear all step 1 session data
        session()->forget([
            'pick_date_step1.name',
            'pick_date_step1.price',
            'pick_date_step1.link',
            'pick_date_step1.details',
            'pick_date_step1.starting_amount',
            'pick_date_step1.preview',
            'pick_date_step1.currency',
        ]);

            // Clear step 3 session data
            session()->forget([
                'pick_date_step3.date',
                'pick_date_step3.calculations'
            ]);

        session()->flash('success', __('You cleared the form.'));

    } catch (\Exception $e) {
        // If anything goes wrong, set error message
        session()->flash('error', __('There was an error during clearing the form. Refresh the page and try again.'));
    }

        return redirect()->back();
    })->name('clear');

    Route::post('/api/link-preview', [PiggyBankCreateController::class, 'fetchLinkPreview'])
        ->name('api.link-preview');


    Route::post('/cancel', [PiggyBankCreateController::class, 'cancelCreation'])
        ->name('cancel');

    Route::get('/step-2', [PiggyBankCreateController::class, 'showStep2'])->name('step-2.get');
    Route::post('/step-2', [PiggyBankCreateController::class, 'step2'])->name('step-2');

    // Strategy-specific Step 3 routes
    Route::post('/choose-strategy', [PiggyBankCreateController::class, 'storeStrategySelection'])->name('choose-strategy');
    Route::prefix('pick-date')->name('pick-date.')->group(function () {
        Route::get('/step-3', [PiggyBankCreateController::class, 'renderStrategyView'])->name('step-3');
        Route::post('/calculate-frequencies', [PiggyBankCreateController::class, 'calculateFrequencyOptions'])->name('calculate-frequencies');
        Route::post('/store-frequency', [PiggyBankCreateController::class, 'storeSelectedFrequency'])->name('store-frequency');
        Route::post('/show-summary', [PiggyBankCreateController::class, 'showSummary'])->name('show-summary');
        Route::get('/summary', [PiggyBankCreateController::class, 'showSummary'])->name('summary');
        Route::post('/store', [PiggyBankCreateController::class, 'storePiggyBank'])
            ->middleware(['auth', 'verified'])
            ->name('store');
    });

    // Flash message check route
    Route::get('/check-flash-messages', function() {
        return view('components.flash-message');
    })->name('check-flash-messages');


    Route::prefix('enter-saving-amount')->name('enter-saving-amount.')->group(function () {
        Route::get('/step-3', [PiggyBankCreateController::class, 'renderStrategyView'])->name('step-3');
    });
});

Route::patch('scheduled-savings/{periodicSaving}', [App\Http\Controllers\ScheduledSavingController::class, 'update'])
    ->name('scheduled-savings.update');

Route::get('/format-date', function (Request $request) {
    $date = $request->query('date'); // Correct usage of query() method

    if (!$date) {
        return response()->json(['error' => 'Invalid date'], 400);
    }

    try {
        // Parse the date into a DateTime object
        $dateObject = new DateTime($date);

        // Format the date based on the app's current locale
        $locale = app()->getLocale(); // Get the current locale
        $formatter = new IntlDateFormatter(
            $locale, // Locale, e.g., 'en', 'fr', 'tr'
            IntlDateFormatter::LONG, // Use a long date format
            IntlDateFormatter::NONE // No time format
        );
        $formattedDate = $formatter->format($dateObject);

        return response()->json(['formatted_date' => $formattedDate]);
    } catch (Exception $e) {
        return response()->json(['error' => 'Date formatting failed: ' . $e->getMessage()], 500);
    }
});

Route::post('/update-timezone', [UserPreferencesController::class, 'updateTimezone'])
    ->name('update-timezone')
    ->middleware(['auth', 'verified']);

Route::get('/test-money', function() {
    try {
        // Test XOF with decimal
        $xof_decimal = Money::of('1000.00', 'XOF');
        dump("XOF with decimal: " . $xof_decimal->getAmount());
        dump("XOF with decimal (minor units): " . $xof_decimal->getMinorAmount()->toInt());

        // Test XOF without decimal
        $xof_whole = Money::of('1000', 'XOF');
        dump("XOF without decimal: " . $xof_whole->getAmount());
        dump("XOF without decimal (minor units): " . $xof_whole->getMinorAmount()->toInt());

        // Compare with EUR for reference
        $eur = Money::of('1000.00', 'EUR');
        dump("EUR: " . $eur->getAmount());
        dump("EUR (minor units): " . $eur->getMinorAmount()->toInt());

    } catch (\Exception $e) {
        dump("Error: " . $e->getMessage());
    }
});


Route::get('/test-currency-helper', function () {
    // Test with XOF (should return false)
    dump('XOF has decimals: ' . (App\Helpers\CurrencyHelper::hasDecimalPlaces('XOF') ? 'true' : 'false'));

    // Test with EUR (should return true)
    dump('EUR has decimals: ' . (App\Helpers\CurrencyHelper::hasDecimalPlaces('EUR') ? 'true' : 'false'));

    // Test with your current session currency
    $currentCurrency = session('currency', config('app.default_currency'));
    dump('Current currency (' . $currentCurrency . ') has decimals: ' .
        (App\Helpers\CurrencyHelper::hasDecimalPlaces($currentCurrency) ? 'true' : 'false'));
});

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


require __DIR__.'/auth.php';
