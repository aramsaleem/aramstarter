<?php

use App\Livewire\Admin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel
|--------------------------------------------------------------------------
|
| Requires the "admin.access" permission. Super Admins pass every check,
| see AppServiceProvider. The "can" middleware is re-applied by Livewire
| on every component update, so checks can't be bypassed after page load.
|
*/

Route::middleware(['auth', 'verified', 'can:admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\Dashboard::class)->name('dashboard');

        Route::get('users', Admin\Users\Index::class)->middleware('can:users.view')->name('users.index');
        Route::get('users/create', Admin\Users\Create::class)->middleware('can:users.create')->name('users.create');
        Route::get('users/{user}/edit', Admin\Users\Edit::class)->middleware('can:users.update')->name('users.edit');

        Route::get('roles', Admin\Roles\Index::class)->middleware('can:roles.view')->name('roles.index');
        Route::get('roles/create', Admin\Roles\Create::class)->middleware('can:roles.create')->name('roles.create');
        Route::get('roles/{role}/edit', Admin\Roles\Edit::class)->middleware('can:roles.update')->name('roles.edit');

        Route::get('permissions', Admin\Permissions\Index::class)->middleware('can:permissions.view')->name('permissions.index');

        Route::get('activity', Admin\Activity\Index::class)->middleware('can:activity.view')->name('activity.index');

        Route::middleware('can:content.manage')->prefix('website')->name('website.')->group(function () {
            Route::get('/', Admin\Website\Settings::class)->name('settings');
            Route::get('features', Admin\Website\Features::class)->name('features');
            Route::get('pricing', Admin\Website\Plans::class)->name('plans');
            Route::get('faq', Admin\Website\Faqs::class)->name('faqs');
        });
    });
