<?php

use App\Enums\SystemPermission;
use App\Enums\SystemRole;
use App\Livewire\Admin\Users;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->superAdmin = User::factory()->superAdmin()->create();
    $this->actingAs($this->superAdmin);
});

function createUserForm(array $overrides = []): Testable
{
    $component = Livewire::test(Users\Create::class)
        ->set('form.name', 'New User')
        ->set('form.email', 'new@example.com')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password');

    foreach ($overrides as $property => $value) {
        $component->set($property, $value);
    }

    return $component;
}

test('users can be listed and searched', function () {
    User::factory()->create(['name' => 'Alice Example']);
    User::factory()->create(['name' => 'Bob Sample']);

    Livewire::test(Users\Index::class)
        ->assertSee('Alice Example')
        ->assertSee('Bob Sample')
        ->set('search', 'Alice')
        ->assertSee('Alice Example')
        ->assertDontSee('Bob Sample');
});

test('users can be filtered by role and status', function () {
    User::factory()->admin()->create(['name' => 'Alice Admin']);
    User::factory()->unverified()->create(['name' => 'Una Verified']);

    Livewire::test(Users\Index::class)
        ->set('role', SystemRole::Admin->value)
        ->assertSee('Alice Admin')
        ->assertDontSee('Una Verified')
        ->set('role', '')
        ->set('status', 'unverified')
        ->assertSee('Una Verified')
        ->assertDontSee('Alice Admin');
});

test('the search input is wired to the component', function () {
    Livewire::test(Users\Index::class)->assertPropertyWired('search');
});

test('a user can be created with roles', function () {
    createUserForm(['form.roles' => [SystemRole::Admin->value]])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'new@example.com')->sole();

    expect($user->hasRole(SystemRole::Admin))->toBeTrue()
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and(Hash::check('password', $user->password))->toBeTrue();
});

test('a new user needs a password', function () {
    createUserForm(['form.password' => '', 'form.password_confirmation' => ''])
        ->call('save')
        ->assertHasErrors(['form.password' => 'required']);
});

test('a user can be updated', function () {
    $user = User::factory()->create();

    Livewire::test(Users\Edit::class, ['user' => $user])
        ->assertSet('form.email', $user->email)
        ->set('form.name', 'Renamed')
        ->set('form.locale', 'ckb')
        ->set('form.roles', [SystemRole::Admin->value])
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Renamed')
        ->and($user->locale)->toBe('ckb')
        ->and($user->getRoleNames()->all())->toBe([SystemRole::Admin->value]);
});

test('the password only changes when a new one is entered', function () {
    $user = User::factory()->create();

    Livewire::test(Users\Edit::class, ['user' => $user])
        ->set('form.name', 'Renamed')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();

    Livewire::test(Users\Edit::class, ['user' => $user])
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'new-password')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('a user can be marked as unverified', function () {
    $user = User::factory()->create();

    Livewire::test(Users\Edit::class, ['user' => $user])
        ->set('form.verified', false)
        ->call('save');

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});

test('a user can be deleted', function () {
    $user = User::factory()->create();

    Livewire::test(Users\Index::class)
        ->call('delete', ['user' => $user->id]);

    expect($user->fresh())->toBeNull();
});

test('deleting asks for confirmation first', function () {
    $user = User::factory()->create();

    Livewire::test(Users\Index::class)->call('confirmDelete', $user->id);

    expect($user->fresh())->not->toBeNull();
});

test('you can not delete your own account from the admin panel', function () {
    Livewire::test(Users\Index::class)
        ->call('delete', ['user' => $this->superAdmin->id])
        ->assertForbidden();

    expect($this->superAdmin->fresh())->not->toBeNull();
});

test('two-factor authentication can be reset for a user', function () {
    $user = User::factory()->withTwoFactor()->create();

    Livewire::test(Users\Edit::class, ['user' => $user])->call('resetTwoFactor');

    expect($user->refresh()->hasEnabledTwoFactorAuthentication())->toBeFalse();
});

test('the only super admin keeps the super admin role', function () {
    Livewire::test(Users\Edit::class, ['user' => $this->superAdmin])
        ->set('form.roles', [])
        ->call('save')
        ->assertHasErrors('form.roles');

    expect($this->superAdmin->refresh()->isSuperAdmin())->toBeTrue();
});

test('admins can not grant the super admin role', function () {
    $this->actingAs(User::factory()->admin()->create());

    createUserForm(['form.roles' => [SystemRole::SuperAdmin->value]])
        ->call('save')
        ->assertHasErrors('form.roles.0');

    expect(User::where('email', 'new@example.com')->exists())->toBeFalse();
});

test('admins can not edit or delete super admins', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.users.edit', $this->superAdmin))->assertForbidden();

    Livewire::test(Users\Index::class)
        ->call('delete', ['user' => $this->superAdmin->id])
        ->assertForbidden();
});

test('users without the delete permission can not delete users', function () {
    $support = Role::create(['name' => 'Support', 'guard_name' => 'web']);
    $support->givePermissionTo([SystemPermission::AccessAdminPanel->value, SystemPermission::ViewUsers->value]);
    $this->actingAs(User::factory()->create()->assignRole($support));

    $user = User::factory()->create();

    Livewire::test(Users\Index::class)
        ->call('delete', ['user' => $user->id])
        ->assertForbidden();

    expect($user->fresh())->not->toBeNull();
});
