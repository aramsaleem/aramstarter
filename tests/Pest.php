<?php

use App\Enums\SocialProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests run against a fresh in-memory SQLite database that is seeded
| with the built-in roles and permissions (see tests/TestCase.php).
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * The code the user's authenticator app would show right now.
 */
function totp(User $user): string
{
    return app(Google2FA::class)->getCurrentOtp($user->two_factor_secret);
}

function enableSocialProvider(SocialProvider $provider): void
{
    config([
        "services.{$provider->value}.client_id" => 'test-client-id',
        "services.{$provider->value}.client_secret" => 'test-client-secret',
    ]);
}
