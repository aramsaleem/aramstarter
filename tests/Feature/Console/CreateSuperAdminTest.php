<?php

use App\Models\User;

test('it creates a verified super admin', function () {
    $this->artisan('app:create-super-admin', [
        '--name' => 'Root Admin',
        '--email' => 'root@example.com',
        '--password' => 'secret-password',
    ])->assertSuccessful();

    $user = User::where('email', 'root@example.com')->sole();

    expect($user->isSuperAdmin())->toBeTrue()
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->can('users.delete'))->toBeTrue();
});

test('it asks for missing details', function () {
    $this->artisan('app:create-super-admin')
        ->expectsQuestion('Email address', 'root@example.com')
        ->expectsQuestion('Name', 'Root Admin')
        ->expectsQuestion('Password', 'secret-password')
        ->assertSuccessful();

    expect(User::where('email', 'root@example.com')->sole()->isSuperAdmin())->toBeTrue();
});

test('it promotes an existing user after confirmation', function () {
    $user = User::factory()->create();

    $this->artisan('app:create-super-admin', ['--email' => $user->email])
        ->expectsConfirmation("A user with the email [{$user->email}] already exists. Make them a Super Admin?", 'yes')
        ->assertSuccessful();

    expect($user->fresh()->isSuperAdmin())->toBeTrue();
});

test('it rejects invalid input', function () {
    $this->artisan('app:create-super-admin', [
        '--name' => 'Root Admin',
        '--email' => 'not-an-email',
        '--password' => 'secret-password',
    ])->assertFailed();

    expect(User::count())->toBe(0);
});
