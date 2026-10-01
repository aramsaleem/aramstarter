<?php

use App\Enums\SocialProvider;
use App\Enums\SystemRole;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Exceptions;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    enableSocialProvider(SocialProvider::Google);
});

function fakeGoogleUser(array $attributes = []): void
{
    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-123',
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        ...$attributes,
    ]));
}

test('buttons are only shown for providers that are configured', function () {
    $this->get(route('login'))
        ->assertSee(__('Continue with :provider', ['provider' => 'Google']))
        ->assertDontSee(__('Continue with :provider', ['provider' => 'Facebook']));
});

test('unknown and unconfigured providers are not found', function () {
    $this->get('/auth/github/redirect')->assertNotFound();
    $this->get(route('social.redirect', SocialProvider::Facebook))->assertNotFound();
    $this->get(route('social.callback', SocialProvider::X))->assertNotFound();
});

test('users are redirected to the provider', function () {
    $this->get(route('social.redirect', SocialProvider::Google))
        ->assertRedirectContains('accounts.google.com');
});

test('a new user is registered, verified and logged in', function () {
    fakeGoogleUser();

    $this->get(route('social.callback', SocialProvider::Google))
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'jane@example.com')->sole();

    $this->assertAuthenticatedAs($user);
    expect($user->name)->toBe('Jane Doe')
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->hasPassword())->toBeFalse()
        ->and($user->hasRole(SystemRole::User))->toBeTrue()
        ->and($user->socialAccounts()->sole())
        ->provider->toBe(SocialProvider::Google)
        ->provider_id->toBe('google-123')
        ->token->toBe('fake-token');
});

test('a returning user is recognised by their provider id', function () {
    $account = SocialAccount::factory()->create(['provider' => SocialProvider::Google, 'provider_id' => 'google-123']);

    fakeGoogleUser(['email' => 'a-different-address@example.com']);

    $this->get(route('social.callback', SocialProvider::Google))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($account->user);
    expect(User::count())->toBe(1);
});

test('an existing verified account with the same email is linked', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    fakeGoogleUser();

    $this->get(route('social.callback', SocialProvider::Google));

    $this->assertAuthenticatedAs($user);
    expect($user->socialAccounts()->count())->toBe(1);
});

test('an unverified account with the same email is not taken over', function () {
    $user = User::factory()->unverified()->create(['email' => 'jane@example.com']);

    fakeGoogleUser();

    $this->get(route('social.callback', SocialProvider::Google))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error');

    $this->assertGuest();
    expect($user->socialAccounts()->count())->toBe(0);
});

test('providers that do not share an email address are refused', function () {
    fakeGoogleUser(['email' => null]);

    $this->get(route('social.callback', SocialProvider::Google))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error');

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('users with two-factor authentication still have to pass the challenge', function () {
    $user = User::factory()->withTwoFactor()->create(['email' => 'jane@example.com']);

    fakeGoogleUser();

    $this->get(route('social.callback', SocialProvider::Google))
        ->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    expect(session('login.id'))->toBe($user->id);
});

test('signed in users can connect a provider to their account', function () {
    $user = User::factory()->create();

    fakeGoogleUser(['id' => 'google-456', 'email' => 'another-address@example.com']);

    $this->actingAs($user)
        ->get(route('social.callback', SocialProvider::Google))
        ->assertRedirect(route('settings.connected-accounts'));

    expect($user->socialAccounts()->sole()->provider_id)->toBe('google-456');
});

test('a provider account that belongs to someone else can not be connected', function () {
    SocialAccount::factory()->create(['provider' => SocialProvider::Google, 'provider_id' => 'google-123']);
    $user = User::factory()->create();

    fakeGoogleUser();

    $this->actingAs($user)
        ->get(route('social.callback', SocialProvider::Google))
        ->assertRedirect(route('settings.connected-accounts'))
        ->assertSessionHas('alert.icon', 'error');

    expect($user->socialAccounts()->count())->toBe(0);
});

test('a failed provider response sends the user back to the login page', function () {
    Exceptions::fake();

    // The real driver throws because the OAuth state is missing from the session.
    $this->get(route('social.callback', SocialProvider::Google))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error');

    $this->assertGuest();
    Exceptions::assertReported(InvalidStateException::class);
});
