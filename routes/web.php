<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\LocaleController;
use App\Livewire\Settings;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::post('locale', LocaleController::class)->name('locale.update');

Route::post('impersonation/leave', [ImpersonationController::class, 'leave'])
    ->middleware('auth')
    ->name('impersonation.leave');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->prefix('settings')->name('settings.')->group(function () {
    Route::redirect('/', '/settings/profile')->name('index');

    Route::get('profile', Settings\Profile::class)->name('profile');
    // Credentials stay out of reach while an admin is signed in as this user.
    Route::middleware('not-impersonating')->group(function () {
        Route::get('password', Settings\Password::class)->name('password');
        Route::get('two-factor', Settings\TwoFactorAuthentication::class)
            ->middleware('password.confirm')
            ->name('two-factor');
        Route::get('connected-accounts', Settings\ConnectedAccounts::class)->name('connected-accounts');
    });
    Route::get('appearance', Settings\Appearance::class)->name('appearance');
    Route::get('language', Settings\Language::class)->name('language');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
