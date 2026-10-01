<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

test('login screen can be rendered', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSeeLivewire(Login::class);
});

test('login form is wired to the component', function () {
    Livewire::test(Login::class)
        ->assertPropertyWired('email')
        ->assertPropertyWired('password')
        ->assertPropertyWired('remember')
        ->assertMethodWiredToForm('login');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users can not authenticate with an invalid password', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('accounts without a password can not log in with a password', function () {
    $user = User::factory()->withoutPassword()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'anything')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('login is rate limited after five failed attempts', function () {
    $user = User::factory()->create();

    $component = Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password');

    foreach (range(1, 5) as $attempt) {
        $component->call('login');
    }

    $component->set('password', 'password')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('users with two-factor authentication are sent to the challenge instead of being logged in', function () {
    $user = User::factory()->withTwoFactor()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    expect(session('login.id'))->toBe($user->id);
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect('/');

    $this->assertGuest();
});
