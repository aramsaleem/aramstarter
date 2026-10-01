<?php

use App\Livewire\Settings\DeleteUserForm;
use App\Livewire\Settings\Profile;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('settings.profile'))
        ->assertOk()
        ->assertSeeLivewire(DeleteUserForm::class);
});

test('profile information can be updated', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('the email address must not belong to another user', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::actingAs(User::factory()->create())
        ->test(Profile::class)
        ->set('email', 'taken@example.com')
        ->call('updateProfileInformation')
        ->assertHasErrors(['email' => 'unique']);
});

test('users can delete their account', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DeleteUserForm::class)
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    $this->assertGuest();
});

test('the correct password must be provided to delete the account', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DeleteUserForm::class)
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors('password');

    expect($user->fresh())->not->toBeNull();
});

test('the only super admin can not delete their account', function () {
    $user = User::factory()->superAdmin()->create();

    Livewire::actingAs($user)
        ->test(DeleteUserForm::class)
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasErrors('password');

    expect($user->fresh())->not->toBeNull();
});
