<?php

use App\Livewire\Settings\Password as PasswordSettings;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('password page is displayed', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('settings.password'))
        ->assertOk();
});

test('password can be updated', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PasswordSettings::class)
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('the current password must be correct', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PasswordSettings::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors('current_password');

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

test('social login accounts can set a password without a current one', function () {
    $user = User::factory()->withoutPassword()->create();

    $this->actingAs($user)
        ->get(route('settings.password'))
        ->assertSee(__('Set a password'));

    Livewire::actingAs($user)
        ->test(PasswordSettings::class)
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});
