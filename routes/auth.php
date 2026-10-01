<?php

use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Actions\Logout;
use App\Livewire\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', Auth\Login::class)->name('login');
    Route::get('register', Auth\Register::class)->name('register');
    Route::get('forgot-password', Auth\ForgotPassword::class)->name('password.request');
    Route::get('reset-password/{token}', Auth\ResetPassword::class)->name('password.reset');
    Route::get('two-factor-challenge', Auth\TwoFactorChallenge::class)->name('two-factor.login');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', Auth\VerifyEmail::class)->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::get('confirm-password', Auth\ConfirmPassword::class)->middleware('not-impersonating')->name('password.confirm');

    Route::post('logout', Logout::class)->name('logout');
});

// Used both to sign in and, when already signed in, to connect a provider from the settings page.
Route::middleware(['throttle:10,1', 'not-impersonating'])->group(function () {
    Route::get('auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])->name('social.redirect');
    Route::get('auth/{provider}/callback', [SocialLoginController::class, 'callback'])->name('social.callback');
});
