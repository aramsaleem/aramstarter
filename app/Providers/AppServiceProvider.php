<?php

namespace App\Providers;

use App\Http\Middleware\PreventDuringImpersonation;
use App\Listeners\RecordAuthenticationActivity;
use App\Models\User;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureSafety();
        $this->configureAuthorization();

        // Security audit trail for sign-ins, failures and account changes.
        Event::listen([Login::class, Logout::class, Failed::class, Lockout::class, Registered::class, Verified::class, PasswordReset::class], RecordAuthenticationActivity::class);

        // Re-check these when Livewire components update too, not only on page load.
        Livewire::addPersistentMiddleware([EnsureEmailIsVerified::class, AuthenticateSession::class, PreventDuringImpersonation::class]);
    }

    /**
     * Strict Eloquent models and other guard rails that turn silent bugs into loud errors.
     */
    protected function configureSafety(): void
    {
        // Outside production: throw on lazy loading (N+1 queries), on assigning attributes that
        // aren't fillable, and on reading attributes that weren't selected or don't exist.
        Model::shouldBeStrict(! $this->app->isProduction());

        // Block migrate:fresh, db:wipe and friends in production.
        DB::prohibitDestructiveCommands($this->app->isProduction());

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(8)->letters()->mixedCase()->numbers()->uncompromised()
            : Password::min(8));
    }

    protected function configureAuthorization(): void
    {
        // Super Admins get every permission. Gate::after (instead of Gate::before) means a policy
        // that explicitly says no - like deleting your own account from the admin panel - still applies.
        Gate::after(fn (User $user) => $user->isSuperAdmin() ? true : null);

        Gate::policy(config('permission.models.role'), RolePolicy::class);
        Gate::policy(config('permission.models.permission'), PermissionPolicy::class);
    }
}
