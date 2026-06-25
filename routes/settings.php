<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::prefix('satgas')
        ->name('satgas.')
        ->group(function () {
            Route::prefix('settings')
                ->name('settings.')
                ->group(function () {
                    Route::prefix('profile')
                        ->name('profile.')
                        ->group(function () {
                            Route::get('', [ProfileController::class, 'edit'])->name('edit');
                            Route::post('request-edit', [ProfileController::class, 'requestEdit'])
                                ->name('request-edit');
                            Route::get('secure-edit', [ProfileController::class, 'secureEdit'])
                                ->middleware('signed')
                                ->name('secure-edit');
                        });
                    Route::prefix('security')
                        ->name('security.')
                        ->group(function () {
                            Route::put('', [ChangePasswordController::class, 'update'])->name('');
                            Route::get('', [SecurityController::class, 'edit'])->name('edit');
                        });
                    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
                    Route::inertia('appearance', 'settings/Appearance')->name('appearance.edit');
                });

            Route::redirect('settings', '/settings/profile');
        });
});
 