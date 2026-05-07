<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Master\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::inertia('/dashboard', 'dashboard/Dashboard')->name('dashboard');

    Route::inertia('/getting-started', 'GettingStarted')
        ->name('getting-started');

    Route::put(
        '/change-password',
        [ChangePasswordController::class, 'update']
    )->name('change-password.update');

    Route::prefix('master')
        ->name('master.')
        ->group(function () {
            Route::resource('users', UserController::class);
        });
});

require __DIR__ . '/settings.php';
