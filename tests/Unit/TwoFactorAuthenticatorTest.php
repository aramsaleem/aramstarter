<?php

use App\Models\User;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->authenticator = app(TwoFactorAuthenticator::class);
    $this->user = User::factory()->make(['id' => 1, 'email' => 'jane@example.com']);
    $this->user->two_factor_secret = $this->authenticator->generateSecretKey();
});

test('secrets are long enough for 160-bit keys', function () {
    expect($this->user->two_factor_secret)->toHaveLength(32)->toMatch('/^[A-Z2-7]+$/');
});

test('the current code is accepted', function () {
    expect($this->authenticator->verify($this->user, totp($this->user)))->toBeTrue();
});

test('codes are accepted with spaces', function () {
    $code = totp($this->user);

    expect($this->authenticator->verify($this->user, substr($code, 0, 3).' '.substr($code, 3)))->toBeTrue();
});

test('wrong and malformed codes are rejected', function (string $code) {
    expect($this->authenticator->verify($this->user, $code))->toBeFalse();
})->with(['000000', '12345', 'abcdef', '']);

test('a code can not be replayed', function () {
    $code = totp($this->user);

    expect($this->authenticator->verify($this->user, $code))->toBeTrue()
        ->and($this->authenticator->verify($this->user, $code))->toBeFalse();
});

test('users without a secret can not verify', function () {
    $this->user->two_factor_secret = null;

    expect($this->authenticator->verify($this->user, '123456'))->toBeFalse();
});

test('recovery codes are unique and readable', function () {
    $codes = $this->authenticator->generateRecoveryCodes();

    expect($codes)->toHaveCount(TwoFactorAuthenticator::RECOVERY_CODE_COUNT)
        ->and(array_unique($codes))->toHaveCount(TwoFactorAuthenticator::RECOVERY_CODE_COUNT)
        ->each->toMatch('/^[a-z0-9]{5}-[a-z0-9]{5}$/');
});

test('the QR code is an inline svg for an otpauth url', function () {
    expect($this->authenticator->qrCodeUrl($this->user))
        ->toStartWith('otpauth://totp/')
        ->toContain(rawurlencode('jane@example.com'))
        ->and($this->authenticator->qrCodeSvg($this->user))
        ->toStartWith('<svg')
        ->not->toContain('<?xml');
});

afterEach(fn () => Cache::flush());
