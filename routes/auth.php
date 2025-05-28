<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Non-localized login redirect for middleware
Route::get('login', function () {
    $locale = Auth::check() ? Auth::user()->language : (session('locale') ?? 'en');

    $availableLocales = array_keys(config('app.available_languages', []));
    if (! in_array($locale, $availableLocales)) {
        $locale = 'en';
    }

    return redirect("/$locale/login");
})->name('login');

Route::prefix('{locale}')
    ->middleware('locale')
    ->where(['locale' => '[a-z]{2}'])
    ->group(function () {

    Route::middleware('guest')->group(function () {

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('localized.register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('localized.login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('localized.password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('localized.password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('localized.password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('localized.password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('localized.verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('localized.verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('localized.verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('localized.password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('localized.password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('localized.logout');
});

});


// Add non-localized password reset routes for language persistence
Route::middleware('guest', 'locale')->group(function () {
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

