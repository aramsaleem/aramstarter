<?php

use App\Enums\SocialProvider;
use App\Livewire\Settings\ConnectedAccounts;
use App\Models\SocialAccount;
use App\Models\User;
use Livewire\Livewire;

test('the page lists the configured providers', function () {
    enableSocialProvider(SocialProvider::Google);

    $this->actingAs(User::factory()->create())
        ->get(route('settings.connected-accounts'))
        ->assertOk()
        ->assertSee('Google')
        ->assertDontSee('Facebook');
});

test('the page explains how to turn social login on when nothing is configured', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('settings.connected-accounts'))
        ->assertOk()
        ->assertSee(__('Social login is not configured'));
});

test('a connected provider can be disconnected', function () {
    enableSocialProvider(SocialProvider::Google);

    $user = User::factory()->create();
    SocialAccount::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(ConnectedAccounts::class)
        ->call('disconnect', ['provider' => SocialProvider::Google->value]);

    expect($user->socialAccounts()->count())->toBe(0);
});

test('the only way to log in can not be disconnected', function () {
    enableSocialProvider(SocialProvider::Google);

    $user = User::factory()->withoutPassword()->create();
    SocialAccount::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(ConnectedAccounts::class)
        ->call('disconnect', ['provider' => SocialProvider::Google->value]);

    expect($user->socialAccounts()->count())->toBe(1);
});

test('users can only disconnect their own accounts', function () {
    enableSocialProvider(SocialProvider::Google);

    $otherAccount = SocialAccount::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test(ConnectedAccounts::class)
        ->call('disconnect', ['provider' => SocialProvider::Google->value]);

    expect($otherAccount->fresh())->not->toBeNull();
});
