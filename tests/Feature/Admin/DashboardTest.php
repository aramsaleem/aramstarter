<?php

use App\Enums\ActivityEvent;
use App\Enums\SystemPermission;
use App\Livewire\Admin\Dashboard;
use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('the dashboard counts sign-ups, sign-ins and threats for the chosen period', function () {
    $admin = User::factory()->superAdmin()->create();
    User::factory()->count(2)->create();
    User::factory()->create(['created_at' => now()->subDays(20)]);

    ActivityLog::create(['event' => ActivityEvent::Login, 'user_id' => $admin->id]);
    ActivityLog::create(['event' => ActivityEvent::LoginFailed, 'properties' => ['email' => 'x@example.com']]);
    ActivityLog::create(['event' => ActivityEvent::Lockout]);

    $component = Livewire::actingAs($admin)->test(Dashboard::class)->set('period', '7');

    expect($component->get('stats'))->toMatchArray([
        'users' => 4,
        'new' => 3,
        'sign_ins' => 1,
        'threats' => 2,
    ]);

    $component->set('period', '30');

    expect($component->get('stats')['new'])->toBe(4)
        ->and($component->get('series'))->toHaveCount(30);
});

test('the 90 day view groups the chart by week', function () {
    $component = Livewire::actingAs(User::factory()->superAdmin()->create())
        ->test(Dashboard::class)
        ->set('period', '90');

    expect($component->get('series'))->toHaveCount(13)
        ->and(array_sum(array_column($component->get('series'), 'signups')))->toBe(1);
});

test('an unknown period falls back to 30 days', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('admin.dashboard', ['period' => '99999']))
        ->assertOk()
        ->assertSee(__('Here is what happened in the last :days days.', ['days' => 30]));
});

test('picking a person shows their details', function () {
    $admin = User::factory()->superAdmin()->create();
    $person = User::factory()->create(['name' => 'Dara Aziz']);
    ActivityLog::create(['event' => ActivityEvent::Login, 'user_id' => $person->id]);

    Livewire::actingAs($admin)->test(Dashboard::class)
        ->call('selectUser', $person->id)
        ->assertSee('Dara Aziz')
        ->assertSee(__('Signed in'));
});

test('people outside the list can not be picked', function () {
    $admin = User::factory()->superAdmin()->create();
    $hidden = User::factory()->create(['name' => 'Hidden Person']);

    Livewire::actingAs($admin)->test(Dashboard::class)
        ->set('search', $admin->email)
        ->call('selectUser', $hidden->id)
        ->assertDontSee('Hidden Person');
});

test('the people and activity panels respect permissions', function () {
    $role = Role::create(['name' => 'Viewer', 'guard_name' => 'web']);
    $role->givePermissionTo(SystemPermission::AccessAdminPanel->value);

    User::factory()->create(['name' => 'Someone Private']);
    ActivityLog::create(['event' => ActivityEvent::LoginFailed, 'properties' => ['email' => 'probe@example.com']]);

    $this->actingAs(User::factory()->create()->assignRole($role))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Someone Private')
        ->assertDontSee('probe@example.com')
        ->assertSee(__('Security checklist'));
});
