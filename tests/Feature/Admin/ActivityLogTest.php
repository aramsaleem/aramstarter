<?php

use App\Enums\ActivityEvent;
use App\Enums\SystemPermission;
use App\Livewire\Admin\Activity;
use App\Livewire\Admin\Users;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\TwoFactorChallenge;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\PendingTwoFactorLogin;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('signing in is recorded', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login');

    $log = ActivityLog::where('event', ActivityEvent::Login)->sole();

    expect($log->user_id)->toBe($user->id)
        ->and($log->ip_address)->toBe('127.0.0.1')
        ->and($log->subjectLabel())->toBe($user->email);
});

test('failed sign-ins keep the attempted email but never the password', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-secret-password')
        ->call('login');

    $log = ActivityLog::where('event', ActivityEvent::LoginFailed)->sole();

    expect($log->properties['email'])->toBe($user->email)
        ->and(json_encode($log->properties))->not->toContain('wrong-secret-password');
});

test('wrong two-factor codes are recorded', function () {
    $user = User::factory()->withTwoFactor()->create();

    PendingTwoFactorLogin::start($user);

    Livewire::test(TwoFactorChallenge::class)
        ->set('code', '000000')
        ->call('authenticate');

    expect(ActivityLog::where('event', ActivityEvent::TwoFactorFailed)->where('user_id', $user->id)->exists())->toBeTrue();
});

test('admin changes record which fields changed, not their values', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Users\Edit::class, ['user' => $user])
        ->set('form.name', 'Renamed Person')
        ->set('form.password', 'a-brand-new-password')
        ->set('form.password_confirmation', 'a-brand-new-password')
        ->call('save')
        ->assertHasNoErrors();

    $log = ActivityLog::where('event', ActivityEvent::UserUpdated)->sole();

    expect($log->user_id)->toBe($admin->id)
        ->and($log->properties['changed'])->toContain('name', 'password')
        ->and(json_encode($log->properties))->not->toContain('a-brand-new-password')
        ->not->toContain('Renamed Person');
});

test('deleted users stay readable in the log', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create(['email' => 'gone@example.com']);

    Livewire::actingAs($admin)->test(Users\Index::class)->call('delete', ['user' => $user->id]);

    expect(ActivityLog::where('event', ActivityEvent::UserDeleted)->sole()->subjectLabel())->toBe('gone@example.com');
});

test('the activity page needs the activity.view permission', function () {
    $role = Role::create(['name' => 'Support', 'guard_name' => 'web']);
    $role->givePermissionTo(SystemPermission::AccessAdminPanel->value);

    $this->actingAs(User::factory()->create()->assignRole($role))
        ->get(route('admin.activity.index'))
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.activity.index'))
        ->assertOk()
        ->assertSee(__('Activity log'));
});

test('the log can be filtered', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->create(['email' => 'target@example.com']);

    ActivityLog::create(['event' => ActivityEvent::LoginFailed, 'properties' => ['email' => 'attacker@example.com'], 'ip_address' => '203.0.113.9']);
    ActivityLog::create(['event' => ActivityEvent::Login, 'user_id' => $target->id, 'properties' => ['label' => $target->email], 'ip_address' => '198.51.100.7']);

    Livewire::actingAs($admin)->test(Activity\Index::class)
        ->call('setGroup', 'threats')
        ->assertSee('attacker@example.com')
        ->assertDontSee('198.51.100.7')
        ->call('setGroup', '')
        ->set('search', '198.51.100')
        ->assertSee('target@example.com')
        ->assertDontSee('attacker@example.com')
        ->set('search', '')
        ->set('event', ActivityEvent::Login->value)
        ->assertSee('198.51.100.7')
        ->assertDontSee('203.0.113.9');
});

test('unknown filter values from the address bar are ignored', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('admin.activity.index', ['group' => "' OR 1=1 --", 'event' => 'nope', 'period' => 'forever']))
        ->assertOk();
});

test('old entries are pruned', function () {
    $old = ActivityLog::create(['event' => ActivityEvent::Login]);
    $old->forceFill(['created_at' => now()->subDays(ActivityLog::RETENTION_DAYS + 1)])->save();
    $recent = ActivityLog::create(['event' => ActivityEvent::Login]);

    $this->artisan('model:prune', ['--model' => [ActivityLog::class]])->assertSuccessful();

    expect(ActivityLog::find($old->id))->toBeNull()
        ->and(ActivityLog::find($recent->id))->not->toBeNull();
});
