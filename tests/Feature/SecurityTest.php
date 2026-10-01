<?php

use App\Enums\SocialProvider;
use App\Enums\SystemPermission;
use App\Enums\SystemRole;
use App\Livewire\Admin\Roles;
use App\Livewire\Admin\Users;
use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use App\Support\PendingTwoFactorLogin;
use Illuminate\Session\Middleware\AuthenticateSession;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

/*
 * Regression tests for the security audit. Each one guards a specific attack.
 */

test('alert titles are rendered as text, so user names can not inject html', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    $victim = User::factory()->create(['name' => '<img src=x onerror=alert(1)>']);

    $component = Livewire::test(Users\Index::class)->call('confirmDelete', $victim->id);

    $js = collect($component->effects['xjs'] ?? [])->pluck('expression')->implode("\n");

    expect($js)->toContain('"titleText"')
        ->not->toContain('"title":');
});

test('admins can not assign roles that are more powerful than themselves', function () {
    $manager = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
    $manager->givePermissionTo(SystemPermission::UpdateRoles->value);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Users\Create::class)
        ->set('form.name', 'Sneaky')
        ->set('form.email', 'sneaky@example.com')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password')
        ->set('form.roles', ['Manager'])
        ->call('save')
        ->assertHasErrors('form.roles.0');

    expect(User::where('email', 'sneaky@example.com')->exists())->toBeFalse();
});

test('admins can not edit users who are more powerful than themselves', function () {
    $manager = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
    $manager->givePermissionTo(SystemPermission::UpdateRoles->value);
    $target = User::factory()->create()->assignRole($manager);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.users.edit', $target))
        ->assertForbidden();
});

test('role editors can only grant permissions they hold themselves', function () {
    Role::findByName(SystemRole::Admin->value)->givePermissionTo(SystemPermission::CreateRoles->value);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Roles\Create::class)
        ->set('form.name', 'Escalated')
        ->set('form.permissions', [SystemPermission::DeletePermissions->value])
        ->call('save')
        ->assertHasErrors('form.permissions.0');

    expect(Role::where('name', 'Escalated')->exists())->toBeFalse();
});

test('a pending two-factor login expires', function () {
    PendingTwoFactorLogin::start(User::factory()->withTwoFactor()->create());

    $this->travel(PendingTwoFactorLogin::LIFETIME_SECONDS + 1)->seconds();

    $this->get(route('two-factor.login'))->assertRedirect(route('login'));
});

test('password reset emails are rate limited', function () {
    $component = Livewire::test(ForgotPassword::class)->set('email', 'someone@example.com');

    foreach (range(1, 5) as $attempt) {
        $component->call('sendPasswordResetLink')->assertHasNoErrors();
    }

    $component->call('sendPasswordResetLink')->assertHasErrors('email');
});

test('responses carry security headers', function () {
    $this->get('/')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('other sessions are signed out when the password changes', function () {
    expect(app('router')->getMiddlewareGroups()['web'])->toContain(AuthenticateSession::class);
});

test('unverified provider emails are never used to find or create accounts', function () {
    enableSocialProvider(SocialProvider::Google);
    $user = User::factory()->create(['email' => 'jane@example.com']);

    Socialite::fake('google', SocialiteUser::fake(['email' => 'jane@example.com'])->setRaw(['email_verified' => false]));

    $this->get(route('social.callback', SocialProvider::Google))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error');

    $this->assertGuest();
    expect($user->socialAccounts()->count())->toBe(0);
});

test('connecting a provider requires a recent password confirmation', function () {
    enableSocialProvider(SocialProvider::Google);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('social.redirect', SocialProvider::Google))
        ->assertRedirect(route('password.confirm'));

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('social.redirect', SocialProvider::Google))
        ->assertRedirectContains('accounts.google.com');
});
