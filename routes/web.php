<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PiggyBankCreateController;
use App\Http\Controllers\PiggyBankController;
use App\Http\Controllers\ProfileController;
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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Language switching route
Route::get('/language/{locale}', function ($locale) {
    $availableLocales = array_values(config('app.available_languages', []));

    if (in_array($locale, $availableLocales)) {
        session()->put('locale', $locale);
    }

    return redirect()->back();
})->name('language.switch');

//Route::get('/current-locale', function () {
//    dd(app()->getLocale());
//});


Route::get('currency/switch/{currency}', function ($currency) {
    $availableCurrencies = config('app.currencies', []);


    if (array_key_exists($currency, $availableCurrencies)) {
        session(['currency' => $currency]);
        $currencyName = __($availableCurrencies[$currency]);


        session()->flash('success', __('You switched the currency to :currency', ['currency' => $currencyName]));
    } else {
        session()->flash('error', __('Invalid currency selected.'));
    }

    return redirect()->back();
})->name('currency.switch');

// Debug route for currency
// visit this to test: http://127.0.0.1:8000/current-currency
Route::get('/current-currency', function () {
    dd(session('currency', config('app.default_currency')));
});


// Create piggy bank routes
Route::middleware(['auth', 'verified'])->prefix('create-piggy-bank')->name('create-piggy-bank.')->group(function () {
    Route::get('/step-1', [PiggyBankCreateController::class, 'step1'])->name('step-1');

    Route::get('/step-2', [PiggyBankCreateController::class, 'showStep2'])->name('step-2.get');

    Route::post('/step-2', [PiggyBankCreateController::class, 'step2'])->name('step-2');

    // Strategy-specific Step 3 routes
    Route::post('/choose-strategy', [PiggyBankCreateController::class, 'storeStrategySelection'])->name('choose-strategy');
    Route::prefix('pick-date')->name('pick-date.')->group(function () {
        Route::get('/step-3', [PiggyBankCreateController::class, 'renderStrategyView'])->name('step-3');
    });
    Route::prefix('enter-saving-amount')->name('enter-saving-amount.')->group(function () {
        Route::get('/step-3', [PiggyBankCreateController::class, 'renderStrategyView'])->name('step-3');
    });
});

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
            $locale, // Locale, e.g., 'en_US', 'fr', 'tr'
            IntlDateFormatter::LONG, // Use a long date format
            IntlDateFormatter::NONE // No time format
        );
        $formattedDate = $formatter->format($dateObject);

        return response()->json(['formatted_date' => $formattedDate]);
    } catch (Exception $e) {
        return response()->json(['error' => 'Date formatting failed'], 500);
    }
});


require __DIR__.'/auth.php';
