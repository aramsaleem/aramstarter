<?php

use App\Enums\SystemRole;
use App\Models\User;

test('guests are sent to the login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('regular users can not open the admin panel', function () {
    $this->actingAs(User::factory()->withRole(SystemRole::User)->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admins can open the admin panel', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(__('Users by role'));
});

test('super admins can open every admin page', function (string $route) {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route($route))
        ->assertOk();
})->with([
    'admin.dashboard',
    'admin.users.index',
    'admin.users.create',
    'admin.roles.index',
    'admin.roles.create',
    'admin.permissions.index',
    'admin.activity.index',
    'admin.website.settings',
    'admin.website.features',
    'admin.website.plans',
    'admin.website.faqs',
]);

test('admins can view but not change roles and permissions by default', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.roles.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.roles.create'))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.permissions.index'))->assertOk();
});

test('unverified users can not open the admin panel', function () {
    $this->actingAs(User::factory()->unverified()->superAdmin()->create())
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('verification.notice'));
});

test('the admin panel link is only shown to users who can use it', function () {
    $this->actingAs(User::factory()->withRole(SystemRole::User)->create())
        ->get(route('dashboard'))
        ->assertDontSee(route('admin.dashboard'));

    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('dashboard'))
        ->assertSee(route('admin.dashboard'));
});
