<?php

use App\Enums\ActivityEvent;
use App\Enums\SystemPermission;
use App\Enums\SystemRole;
use App\Livewire\Admin\Users;
use App\Livewire\Settings\DeleteUserForm;
use App\Livewire\Settings\Profile;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Impersonation;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

/**
 * Sign $admin in as $target the way the "Sign in as user" button does.
 */
function impersonate(User $admin, User $target): void
{
    session(['auth.password_confirmed_at' => time()]);

    Livewire::actingAs($admin)
        ->test(Users\Index::class)
        ->call('impersonate', ['user' => $target->id])
        ->assertRedirect(route('dashboard'));
}

describe('starting', function () {
    test('an admin can sign in as a regular user', function () {
        $admin = User::factory()->superAdmin()->create();
        $target = User::factory()->create();

        impersonate($admin, $target);

        expect(auth()->id())->toBe($target->id)
            ->and(Impersonation::active())->toBeTrue()
            ->and(Impersonation::impersonator()->is($admin))->toBeTrue();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Signed in as :name', ['name' => $target->name]))
            ->assertSee(__('Return to my account'));
    });

    test('it is recorded, without a fake sign-in for the target', function () {
        $admin = User::factory()->superAdmin()->create();
        $target = User::factory()->create();

        impersonate($admin, $target);

        $log = ActivityLog::where('event', ActivityEvent::ImpersonationStarted)->sole();

        expect($log->user_id)->toBe($admin->id)
            ->and($log->subject_id)->toBe($target->id)
            ->and(ActivityLog::where('event', ActivityEvent::Login)->exists())->toBeFalse();
    });

    test('a recent password confirmation is required', function () {
        $admin = User::factory()->superAdmin()->create();
        $target = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(Users\Edit::class, ['user' => $target])
            ->call('impersonate', ['user' => $target->id])
            ->assertRedirect(route('password.confirm'));

        expect(auth()->id())->toBe($admin->id)
            ->and(Impersonation::active())->toBeFalse();
    });

    test('the admin role can impersonate by default, other roles need the permission', function () {
        $target = User::factory()->create();

        expect(User::factory()->admin()->create()->can('impersonate', $target))->toBeTrue();

        $role = Role::create(['name' => 'Support', 'guard_name' => 'web']);
        $role->givePermissionTo([SystemPermission::AccessAdminPanel->value, SystemPermission::ViewUsers->value]);

        expect(User::factory()->create()->assignRole($role)->can('impersonate', $target))->toBeFalse();
    });

    test('nobody can impersonate a more powerful user or themselves', function () {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        expect($admin->can('impersonate', $superAdmin))->toBeFalse()
            ->and($admin->can('impersonate', $admin))->toBeFalse()
            ->and($superAdmin->can('impersonate', $superAdmin))->toBeFalse();

        Livewire::actingAs($admin)
            ->test(Users\Index::class)
            ->call('impersonate', ['user' => $superAdmin->id])
            ->assertForbidden();
    });

    test('impersonation can not be nested', function () {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();

        impersonate($superAdmin, $admin);

        expect($admin->fresh()->can('impersonate', $other))->toBeFalse();

        Livewire::test(Users\Index::class)
            ->call('confirmImpersonate', $other->id)
            ->assertForbidden();
    });
});

describe('while signed in as someone else', function () {
    beforeEach(function () {
        $this->admin = User::factory()->superAdmin()->create(['email' => 'boss@example.com']);
        $this->target = User::factory()->create();

        impersonate($this->admin, $this->target);
    });

    test('the admin\'s password confirmation does not carry over', function () {
        expect(session()->has('auth.password_confirmed_at'))->toBeFalse();
    });

    test('credentials are out of reach', function (string $route) {
        $this->get(route($route))->assertForbidden();
    })->with(['settings.password', 'settings.two-factor', 'settings.connected-accounts', 'password.confirm']);

    test('the account can not be deleted', function () {
        $this->get(route('settings.profile'))->assertOk()->assertDontSeeLivewire(DeleteUserForm::class);

        Livewire::test(DeleteUserForm::class)
            ->set('password', 'password')
            ->call('deleteUser')
            ->assertForbidden();

        expect($this->target->fresh())->not->toBeNull();
    });

    test('changes are traced back to the admin', function () {
        Livewire::test(Profile::class)
            ->set('name', 'Changed While Impersonating')
            ->call('updateProfileInformation');

        $log = ActivityLog::where('event', ActivityEvent::ProfileUpdated)->sole();

        expect($log->user_id)->toBe($this->target->id)
            ->and($log->properties['impersonated_by'])->toBe('boss@example.com');
    });

    test('the session survives the password check on the next request', function () {
        $this->get(route('settings.profile'))->assertOk();

        expect(auth()->id())->toBe($this->target->id);
    });

    test('returning switches back and is recorded', function () {
        $this->post(route('impersonation.leave'))
            ->assertRedirect(route('admin.users.edit', $this->target));

        expect(auth()->id())->toBe($this->admin->id)
            ->and(Impersonation::active())->toBeFalse()
            ->and(ActivityLog::where('event', ActivityEvent::ImpersonationEnded)->sole()->user_id)->toBe($this->admin->id);
    });

    test('it ends on its own after the time limit', function () {
        $this->travel(Impersonation::MAX_MINUTES + 1)->minutes();

        $this->get(route('dashboard'))->assertRedirect(route('dashboard'));

        expect(auth()->id())->toBe($this->admin->id)
            ->and(Impersonation::active())->toBeFalse();
    });

    test('an expired session asks Livewire to reload the page', function () {
        $this->travel(Impersonation::MAX_MINUTES + 1)->minutes();

        $this->withHeaders(['X-Livewire' => '1'])->get(route('dashboard'))->assertStatus(419);

        expect(auth()->id())->toBe($this->admin->id);
    });

    test('everyone is signed out if the admin account was deleted meanwhile', function () {
        $this->admin->delete();

        $this->post(route('impersonation.leave'))->assertRedirect('/');

        $this->assertGuest();
    });
});

test('the leave route does nothing for normal sessions', function () {
    $user = User::factory()->withRole(SystemRole::User)->create();

    $this->actingAs($user)->post(route('impersonation.leave'))->assertRedirect(route('dashboard'));

    expect(auth()->id())->toBe($user->id);
});
