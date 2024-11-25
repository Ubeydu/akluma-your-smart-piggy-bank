<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PiggyBankController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

Route::get('/current-locale', function () {
    dd(app()->getLocale());
});

require __DIR__.'/auth.php';
