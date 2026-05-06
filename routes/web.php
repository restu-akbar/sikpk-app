<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

    Route::inertia('/getting-started', 'GettingStarted')
        ->name('getting-started');
    Route::put(
        '/change-password',
        [ChangePasswordController::class, 'update']
    )->name('change-password.update');
});

require __DIR__ . '/settings.php';
