<?php

use App\Enums\SystemPermission;
use App\Livewire\Admin\Permissions;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->actingAs(User::factory()->superAdmin()->create());
});

test('permissions are listed by group', function () {
    Livewire::test(Permissions\Index::class)
        ->assertSee('users.create')
        ->assertSee('roles.view')
        ->assertSee(__('Users'));
});

test('permissions can be searched', function () {
    Livewire::test(Permissions\Index::class)
        ->set('search', 'roles.')
        ->assertSee('roles.view')
        ->assertDontSee('users.create');
});

test('a permission can be created', function () {
    Livewire::test(Permissions\Index::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('name', 'posts.publish')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    expect(Permission::where('name', 'posts.publish')->exists())->toBeTrue();
});

test('permission names must use the group.action format', function (string $name) {
    Livewire::test(Permissions\Index::class)
        ->call('create')
        ->set('name', $name)
        ->call('save')
        ->assertHasErrors(['name' => 'regex']);
})->with(['Posts publish', 'posts..publish', 'posts.', 'POSTS.PUBLISH']);

test('permission names must be unique', function () {
    Livewire::test(Permissions\Index::class)
        ->call('create')
        ->set('name', 'users.view')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('a custom permission can be renamed', function () {
    $permission = Permission::create(['name' => 'posts.publish', 'guard_name' => 'web']);

    Livewire::test(Permissions\Index::class)
        ->call('edit', $permission->id)
        ->assertSet('name', 'posts.publish')
        ->set('name', 'articles.publish')
        ->call('save')
        ->assertHasNoErrors();

    expect($permission->refresh()->name)->toBe('articles.publish');
});

test('a custom permission can be deleted', function () {
    $permission = Permission::create(['name' => 'posts.publish', 'guard_name' => 'web']);

    Livewire::test(Permissions\Index::class)->call('delete', ['permission' => $permission->id]);

    expect($permission->fresh())->toBeNull();
});

test('built-in permissions can not be renamed or deleted', function () {
    $permission = Permission::findByName(SystemPermission::ViewUsers->value);

    Livewire::test(Permissions\Index::class)
        ->call('edit', $permission->id)
        ->assertForbidden();

    Livewire::test(Permissions\Index::class)
        ->call('delete', ['permission' => $permission->id])
        ->assertForbidden();

    expect($permission->fresh())->not->toBeNull();
});
