<?php

use App\Livewire\Auth\ConfirmPassword;
use App\Models\User;
use Livewire\Livewire;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('password.confirm'))
        ->assertOk();
});

test('password can be confirmed', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ConfirmPassword::class)
        ->set('password', 'password')
        ->call('confirmPassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    expect(session('auth.password_confirmed_at'))->not->toBeNull();
});

test('password is not confirmed with an invalid password', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ConfirmPassword::class)
        ->set('password', 'wrong-password')
        ->call('confirmPassword')
        ->assertHasErrors('password');

    expect(session('auth.password_confirmed_at'))->toBeNull();
});

test('users without a password are asked to set one first', function () {
    $user = User::factory()->withoutPassword()->create();

    $this->actingAs($user)
        ->get(route('password.confirm'))
        ->assertOk()
        ->assertSee(__('Set a password first'));
});
