<?php

use App\Livewire\Settings\TwoFactorAuthentication;
use App\Models\User;
use App\Services\TwoFactorAuthenticator;
use Livewire\Livewire;

beforeEach(function () {
    session()->put('auth.password_confirmed_at', time());
});

test('the settings page asks for the password first', function () {
    session()->forget('auth.password_confirmed_at');

    $this->actingAs(User::factory()->create())
        ->get(route('settings.two-factor'))
        ->assertRedirect(route('password.confirm'));
});

test('the settings page is displayed once the password is confirmed', function () {
    $this->actingAs(User::factory()->create())
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('settings.two-factor'))
        ->assertOk()
        ->assertSee(__('Enable two-factor authentication'));
});

test('two-factor authentication can be enabled and confirmed', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(TwoFactorAuthentication::class)
        ->call('enable')
        ->assertSee(__('Setup key'));

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->recoveryCodes())->toHaveCount(TwoFactorAuthenticator::RECOVERY_CODE_COUNT)
        ->and($user->hasEnabledTwoFactorAuthentication())->toBeFalse();

    $component->set('code', totp($user))
        ->call('confirmSetup')
        ->assertHasNoErrors()
        ->assertSet('showingRecoveryCodes', true)
        ->assertSee($user->recoveryCodes()[0]);

    expect($user->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('an invalid code does not enable two-factor authentication', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TwoFactorAuthentication::class)
        ->call('enable')
        ->set('code', '000000')
        ->call('confirmSetup')
        ->assertHasErrors('code');

    expect($user->refresh()->hasEnabledTwoFactorAuthentication())->toBeFalse();
});

test('the setup can be cancelled', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TwoFactorAuthentication::class)
        ->call('enable')
        ->call('cancelSetup');

    expect($user->refresh()->two_factor_secret)->toBeNull();
});

test('recovery codes can be regenerated', function () {
    $user = User::factory()->withTwoFactor()->create();
    $originalCodes = $user->recoveryCodes();

    Livewire::actingAs($user)
        ->test(TwoFactorAuthentication::class)
        ->call('regenerateRecoveryCodes')
        ->assertSet('showingRecoveryCodes', true);

    expect($user->refresh()->recoveryCodes())
        ->toHaveCount(TwoFactorAuthenticator::RECOVERY_CODE_COUNT)
        ->not->toBe($originalCodes);
});

test('disabling asks for confirmation before doing anything', function () {
    $user = User::factory()->withTwoFactor()->create();

    Livewire::actingAs($user)
        ->test(TwoFactorAuthentication::class)
        ->call('confirmDisable');

    expect($user->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('two-factor authentication can be disabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    Livewire::actingAs($user)
        ->test(TwoFactorAuthentication::class)
        ->call('disable');

    $user->refresh();
    expect($user->hasEnabledTwoFactorAuthentication())->toBeFalse()
        ->and($user->two_factor_secret)->toBeNull()
        ->and($user->recoveryCodes())->toBeEmpty();
});

test('actions require the password again once the confirmation has expired', function () {
    session()->put('auth.password_confirmed_at', time() - 20_000);

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TwoFactorAuthentication::class)
        ->call('enable')
        ->assertRedirect(route('password.confirm'));

    expect($user->refresh()->two_factor_secret)->toBeNull();
});
