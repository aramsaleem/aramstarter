<?php

use App\Enums\SystemRole;
use App\Livewire\Admin\Roles;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->actingAs(User::factory()->superAdmin()->create());
});

test('roles are listed', function () {
    Livewire::test(Roles\Index::class)
        ->assertSee(SystemRole::SuperAdmin->value)
        ->assertSee(SystemRole::Admin->value)
        ->assertSee(SystemRole::User->value);
});

test('a role can be created with permissions', function () {
    Livewire::test(Roles\Create::class)
        ->set('form.name', 'Editor')
        ->set('form.permissions', ['users.view', 'users.update'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.roles.index'));

    $role = Role::findByName('Editor');

    expect($role->hasPermissionTo('users.view'))->toBeTrue()
        ->and($role->hasPermissionTo('users.delete'))->toBeFalse();
});

test('role names must be unique', function () {
    Livewire::test(Roles\Create::class)
        ->set('form.name', SystemRole::Admin->value)
        ->call('save')
        ->assertHasErrors(['form.name' => 'unique']);
});

test('only existing permissions can be granted', function () {
    Livewire::test(Roles\Create::class)
        ->set('form.name', 'Editor')
        ->set('form.permissions', ['does.not-exist'])
        ->call('save')
        ->assertHasErrors('form.permissions.0');
});

test('a whole permission group can be toggled', function () {
    Livewire::test(Roles\Create::class)
        ->call('toggleGroup', 'users')
        ->assertSet('form.permissions', ['users.create', 'users.delete', 'users.impersonate', 'users.update', 'users.view'])
        ->call('toggleGroup', 'users')
        ->assertSet('form.permissions', []);
});

test('a role can be updated', function () {
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    Livewire::test(Roles\Edit::class, ['role' => $role])
        ->set('form.name', 'Content editor')
        ->set('form.permissions', ['roles.view'])
        ->call('save')
        ->assertHasNoErrors();

    $role->refresh();

    expect($role->name)->toBe('Content editor')
        ->and($role->permissions->pluck('name')->all())->toBe(['roles.view']);
});

test('built-in roles can not be renamed', function () {
    $role = Role::findByName(SystemRole::User->value);

    Livewire::test(Roles\Edit::class, ['role' => $role])
        ->set('form.name', 'Member')
        ->call('save')
        ->assertHasErrors('form.name');

    expect($role->refresh()->name)->toBe(SystemRole::User->value);
});

test('the super admin role can not be edited', function () {
    $this->get(route('admin.roles.edit', Role::findByName(SystemRole::SuperAdmin->value)))
        ->assertForbidden();
});

test('a custom role can be deleted', function () {
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    Livewire::test(Roles\Index::class)->call('delete', ['role' => $role->id]);

    expect(Role::where('name', 'Editor')->exists())->toBeFalse();
});

test('built-in roles can not be deleted', function () {
    Livewire::test(Roles\Index::class)
        ->call('delete', ['role' => Role::findByName(SystemRole::User->value)->id])
        ->assertForbidden();

    expect(Role::where('name', SystemRole::User->value)->exists())->toBeTrue();
});
