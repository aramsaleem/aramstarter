<?php

namespace App\Livewire\Concerns;

use App\Models\User;
use App\Support\Impersonation;
use Illuminate\Support\Facades\Auth;

/**
 * "Sign in as" buttons for admin pages. Needs InteractsWithAlerts.
 */
trait ImpersonatesUsers
{
    public function confirmImpersonate(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->authorize('impersonate', $user);

        $this->askForConfirmation(
            __('Sign in as :name?', ['name' => $user->name]),
            __('You will see the app exactly as they do, for up to :minutes minutes. Everything you do is recorded in the activity log.', ['minutes' => Impersonation::MAX_MINUTES]),
            'impersonate',
            ['user' => $user->id],
            __('Sign in as user'),
        );
    }

    /**
     * @param  array{user?: int}  $data
     */
    public function impersonate(array $data): void
    {
        $user = User::find($data['user'] ?? null);

        if (! $user) {
            return;
        }

        $this->authorize('impersonate', $user);

        /** @var User $admin */
        $admin = Auth::user();

        // Same rule as other sensitive actions: a recent password confirmation, unless the account has no password.
        $confirmedAt = (int) session('auth.password_confirmed_at', 0);

        if ($admin->hasPassword() && time() - $confirmedAt >= (int) config('auth.password_timeout', 10800)) {
            // Come back to this page afterwards - but only ever to a page on this site.
            $previous = url()->previous();
            session()->put('url.intended', str_starts_with($previous, url('/').'/') ? $previous : route('admin.users.index'));

            $this->redirectRoute('password.confirm');

            return;
        }

        Impersonation::start($admin, $user);

        // A full page load, so every part of the layout is rendered for the new user.
        $this->redirectRoute('dashboard');
    }
}
