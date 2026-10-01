<?php

use App\Enums\SystemRole;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('registration screen can be rendered', function () {
    $this->get(route('register'))->assertOk();
});

test('new users can register and receive the default role', function () {
    Livewire::test(Register::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'jane@example.com')->sole();

    $this->assertAuthenticatedAs($user);
    expect($user->hasRole(SystemRole::User))->toBeTrue()
        ->and($user->hasVerifiedEmail())->toBeFalse();
});

test('registration remembers the language the user signed up in', function () {
    app()->setLocale('ckb');

    Livewire::test(Register::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    expect(User::where('email', 'jane@example.com')->sole()->locale)->toBe('ckb');
});

test('a verification email is sent after registering', function () {
    Notification::fake();

    Livewire::test(Register::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    Notification::assertSentTo(User::where('email', 'jane@example.com')->sole(), VerifyEmail::class);
});

test('the email address must be unused', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    Livewire::test(Register::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasErrors(['email' => 'unique']);
});

test('the password must be confirmed', function () {
    Livewire::test(Register::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'something-else')
        ->call('register')
        ->assertHasErrors(['password' => 'confirmed']);
});
