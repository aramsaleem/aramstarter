<?php

use App\Livewire\Auth\TwoFactorChallenge;
use App\Models\User;
use App\Services\TwoFactorAuthenticator;
use App\Support\PendingTwoFactorLogin;
use Livewire\Livewire;

test('the challenge sends visitors without a pending login back to the login page', function () {
    $this->get(route('two-factor.login'))->assertRedirect(route('login'));
});

test('the challenge screen can be rendered during a login', function () {
    PendingTwoFactorLogin::start(User::factory()->withTwoFactor()->create());

    $this->get(route('two-factor.login'))->assertOk();
});

test('users can finish logging in with a code from their authenticator app', function () {
    $user = User::factory()->withTwoFactor()->create();
    PendingTwoFactorLogin::start($user, remember: true);

    Livewire::test(TwoFactorChallenge::class)
        ->set('code', totp($user))
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    expect(session()->has('login.id'))->toBeFalse();
});

test('an invalid code is rejected', function () {
    $user = User::factory()->withTwoFactor()->create();
    PendingTwoFactorLogin::start($user);

    Livewire::test(TwoFactorChallenge::class)
        ->set('code', '000000')
        ->call('authenticate')
        ->assertHasErrors('code');

    $this->assertGuest();
});

test('a code can only be used once', function () {
    $user = User::factory()->withTwoFactor()->create();
    PendingTwoFactorLogin::start($user);

    $code = totp($user);
    expect(app(TwoFactorAuthenticator::class)->verify($user, $code))->toBeTrue();

    Livewire::test(TwoFactorChallenge::class)
        ->set('code', $code)
        ->call('authenticate')
        ->assertHasErrors('code');

    $this->assertGuest();
});

test('users can log in with a recovery code, which is then used up', function () {
    $user = User::factory()->withTwoFactor()->create();
    $recoveryCode = $user->recoveryCodes()[0];
    PendingTwoFactorLogin::start($user);

    Livewire::test(TwoFactorChallenge::class)
        ->call('toggleRecoveryCode')
        ->assertSet('usingRecoveryCode', true)
        ->set('recovery_code', $recoveryCode)
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->recoveryCodes())
        ->toHaveCount(TwoFactorAuthenticator::RECOVERY_CODE_COUNT - 1)
        ->not->toContain($recoveryCode);
});

test('the challenge is rate limited', function () {
    $user = User::factory()->withTwoFactor()->create();
    PendingTwoFactorLogin::start($user);

    $component = Livewire::test(TwoFactorChallenge::class);

    foreach (range(1, 5) as $attempt) {
        $component->set('code', '000000')->call('authenticate');
    }

    $component->set('code', totp($user))
        ->call('authenticate')
        ->assertHasErrors('code');

    $this->assertGuest();
});
