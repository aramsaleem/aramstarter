<?php

use App\Livewire\Settings\DeleteUserForm;
use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

test('the welcome page renders', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(config('app.name'))
        ->assertSee(route('register'));
});

test('guests are redirected from the dashboard to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('verified users can see the dashboard', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(__('Welcome back, :name!', ['name' => 'Jane']));
});

test('the dashboard nudges users to enable two-factor authentication', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertSee(__('Protect your account'));

    $this->actingAs(User::factory()->withTwoFactor()->create())
        ->get(route('dashboard'))
        ->assertDontSee(__('Protect your account'));
});

test('every page renders in every language', function (string $locale) {
    $superAdmin = User::factory()->superAdmin()->create(['locale' => $locale]);

    $this->get('/')->assertOk();

    $this->actingAs($superAdmin);

    foreach (['dashboard', 'settings.profile', 'settings.password', 'settings.appearance', 'settings.language', 'admin.dashboard', 'admin.users.index', 'admin.roles.index', 'admin.permissions.index'] as $route) {
        $this->get(route($route))->assertOk()->assertSee('lang="'.$locale.'"', false);
    }
})->with(['en', 'ar', 'ckb']);

test('the profile page embeds the delete account component', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Profile::class)
        ->assertContainsLivewireComponent(DeleteUserForm::class);
});
