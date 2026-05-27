<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Master\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['guest:google'])->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect']);
    Route::inertia('/login', 'auth/GoogleLogin')->name('google.login');
});

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::middleware(['auth:google'])->group(function () {
    Route::inertia('/dashboard', 'dashboard/Dashboard')->name('dashboard');
    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('reporter.logout');
});

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::prefix('satgas')
        ->name('satgas.')
        ->group(function () {
            Route::inertia('/dashboard', 'dashboard/Dashboard')->name('dashboard');

            Route::prefix('getting-started')
                ->name('getting-started.')
                ->group(function () {
                    Route::inertia('/', 'GettingStarted')
                        ->name('index');

                    Route::put(
                        '/',
                        [ChangePasswordController::class, 'update']
                    )->name('update');

                    Route::put(
                        '/complete',
                        [ChangePasswordController::class, 'complete']
                    )->name('complete');
                });


            Route::prefix('master')
                ->name('master.')
                ->group(function () {
                    Route::resource('users', UserController::class);
                });
        });
});

require __DIR__ . '/settings.php';
