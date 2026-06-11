<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\AudioRecordingController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\KeyController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Module\ReportController;
use App\Http\Controllers\Module\ReportHandlingController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::inertia('/landing', 'Landing')->name('landing');

Route::middleware(['guest:google'])->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect']);
    Route::inertia('/login', 'auth/GoogleLogin')->name('google.login');
});

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::middleware(['auth:google'])->group(function () {
    Route::inertia('/dashboard', 'dashboard/Dashboard')->name('dashboard');
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::inertia('track', 'reporters/reports/ReportTracking')->name('track');
    });
    Route::resource('reports', ReportController::class);

    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('reporter.logout');

    Route::prefix('api')
        ->name('api.')
        ->group(function () {
            Route::prefix('reports')
                ->name('reports.')
                ->group(function () {
                    Route::get('', [ReportController::class, 'data']);
                    Route::get('{report}/logs', [ReportController::class, 'showLogs']);
                });
        });
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

            Route::prefix('reports')->name('reports.')->group(function () {
                Route::post('assign/{id}', [ReportController::class, 'assign'])->name('assign');
                Route::put('reject/{id}', [ReportController::class, 'reject'])->name('reject');

                Route::prefix('handling')->name('handling.')->group(function () {
                    Route::get('', [ReportHandlingController::class, 'index'])->name('index');
                    Route::get('{id}', [ReportHandlingController::class, 'show'])->name('show');
                });
                Route::post('evidences/{id}', [EvidenceController::class, 'store'])->name('evidence.store');
            });

            Route::resource('reports', ReportController::class);
            Route::get('crypto', [KeyController::class, 'show'])->name('crypto');
            Route::get('evidences/{evidence}', [EvidenceController::class, 'show']);
            Route::get('audio-recordings/{audioRecording}', [AudioRecordingController::class, 'show'])->name('audio-recordings.show');

            Route::prefix('api')
                ->name('api.')
                ->group(function () {
                    Route::get('/users', [UserController::class, 'data'])
                        ->name('users');
                });
        });
});

Route::get('/api/public-key', [KeyController::class, 'publicKey']);

require __DIR__ . '/settings.php';
