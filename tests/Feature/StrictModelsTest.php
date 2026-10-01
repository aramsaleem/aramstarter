<?php

use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;

test('eloquent models are strict outside production', function () {
    expect(Model::preventsLazyLoading())->toBeTrue()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeTrue()
        ->and(Model::preventsAccessingMissingAttributes())->toBeTrue();
});

test('lazy loading a relationship throws', function () {
    User::factory(2)->create();

    User::all()->each(fn (User $user) => $user->socialAccounts);
})->throws(LazyLoadingViolationException::class);

test('assigning attributes that are not fillable throws', function () {
    new User(['is_admin' => true]);
})->throws(MassAssignmentException::class);

test('reading attributes that were not selected throws', function () {
    User::factory()->create();

    User::query()->select('id')->first()->email;
})->throws(MissingAttributeException::class);
