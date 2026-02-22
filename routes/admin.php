<?php

use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'ensure.admin'])
    ->group(function () {
        Route::get('/', [StatsController::class, 'index'])->name('stats');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::patch('/users/{user}/unsuspend', [UserController::class, 'unsuspend'])->name('users.unsuspend');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
