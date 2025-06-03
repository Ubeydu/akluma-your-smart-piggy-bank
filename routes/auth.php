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

// Localized GET routes (forms)
Route::localizedGet('login', [AuthenticatedSessionController::class, 'create'])
    ->name('localized.login')
    ->middleware(['guest']);

// Localized POST routes (form processing)
Route::localizedPost('login', [AuthenticatedSessionController::class, 'store'])
    ->name('localized.login.store')
    ->middleware(['guest']);

// Localized register routes
Route::localizedGet('register', [RegisteredUserController::class, 'create'])
    ->name('localized.register')
    ->middleware(['guest']);

Route::localizedPost('register', [RegisteredUserController::class, 'store'])
    ->name('localized.register.store')
    ->middleware(['guest']);

// Localized forgot password routes
Route::localizedGet('forgot-password', [PasswordResetLinkController::class, 'create'])
    ->name('localized.password.request')
    ->middleware(['guest']);

Route::localizedPost('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('localized.password.email')
    ->middleware(['guest']);

// Localized reset password routes
Route::localizedGet('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('localized.password.reset')
    ->middleware(['guest']);

Route::localizedPost('reset-password', [NewPasswordController::class, 'store'])
    ->name('localized.password.store')
    ->middleware(['guest']);

Route::localizedGet('verify-email', EmailVerificationPromptController::class)
    ->name('localized.verification.notice')
    ->middleware(['auth']);

Route::localizedGet('verify-email-with-params', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('localized.verification.verify');

Route::localizedPost('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('localized.verification.send');

Route::localizedPost('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('localized.logout');

Route::localizedGet('confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware(['auth'])
    ->name('localized.password.confirm');

Route::localizedPost('confirm-password', [ConfirmablePasswordController::class, 'store'])
    ->middleware(['auth'])
    ->name('localized.password.store');

Route::localizedPut('password', [PasswordController::class, 'update'])
    ->middleware(['auth'])
    ->name('localized.password.update');

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

            //            Route::get('register', [RegisteredUserController::class, 'create'])
            //                ->name('localized.register');
            //
            //            Route::post('register', [RegisteredUserController::class, 'store']);

            //    Route::get('login', [AuthenticatedSessionController::class, 'create'])
            //        ->name('localized.login');
            //
            //    Route::post('login', [AuthenticatedSessionController::class, 'store']);


        });


    });

// Add non-localized password reset routes for language persistence
Route::middleware('guest', 'locale')->group(function () {
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});
