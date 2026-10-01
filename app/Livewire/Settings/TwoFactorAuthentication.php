<?php

namespace App\Livewire\Settings;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Models\User;
use App\Services\TwoFactorAuthenticator;
use App\Support\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The route requires a recent password confirmation (password.confirm middleware);
 * every action re-checks it in case the confirmation expired while the page was open.
 */
class TwoFactorAuthentication extends Component
{
    use InteractsWithAlerts;

    public string $code = '';

    public bool $showingRecoveryCodes = false;

    #[Computed]
    public function user(): User
    {
        return Auth::user();
    }

    /**
     * Start the setup: generate a secret and recovery codes. 2FA stays inactive until confirmed.
     */
    public function enable(TwoFactorAuthenticator $authenticator): void
    {
        if (! $this->ensurePasswordIsConfirmed()) {
            return;
        }

        $this->user->forceFill([
            'two_factor_secret' => $authenticator->generateSecretKey(),
            'two_factor_recovery_codes' => $authenticator->generateRecoveryCodes(),
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->reset('code');
        $this->resetErrorBag();
    }

    /**
     * Finish the setup with a code from the authenticator app.
     */
    public function confirmSetup(TwoFactorAuthenticator $authenticator): void
    {
        if (! $this->ensurePasswordIsConfirmed()) {
            return;
        }

        $this->validate(['code' => ['required', 'string']]);

        if ($this->user->two_factor_secret === null || ! $authenticator->verify($this->user, $this->code)) {
            $this->reset('code');
            $this->addError('code', __('The authentication code is invalid.'));

            return;
        }

        $this->user->forceFill(['two_factor_confirmed_at' => now()])->save();

        Audit::log(ActivityEvent::TwoFactorEnabled, $this->user);

        $this->reset('code');
        $this->showingRecoveryCodes = true;

        $this->toast(__('Two-factor authentication enabled.'));
    }

    public function cancelSetup(): void
    {
        if (! $this->user->hasEnabledTwoFactorAuthentication()) {
            $this->user->disableTwoFactorAuthentication();
        }

        $this->reset('code');
        $this->resetErrorBag();
    }

    public function showRecoveryCodes(): void
    {
        if ($this->ensurePasswordIsConfirmed()) {
            $this->showingRecoveryCodes = true;
        }
    }

    public function regenerateRecoveryCodes(TwoFactorAuthenticator $authenticator): void
    {
        if (! $this->ensurePasswordIsConfirmed()) {
            return;
        }

        $this->user->forceFill([
            'two_factor_recovery_codes' => $authenticator->generateRecoveryCodes(),
        ])->save();

        Audit::log(ActivityEvent::RecoveryCodesRegenerated, $this->user);

        $this->showingRecoveryCodes = true;

        $this->toast(__('New recovery codes generated.'));
    }

    public function confirmDisable(): void
    {
        $this->askForConfirmation(
            __('Disable two-factor authentication?'),
            __('Your account will only be protected by your password.'),
            'disable',
            confirmButtonText: __('Disable'),
        );
    }

    public function disable(): void
    {
        if (! $this->ensurePasswordIsConfirmed()) {
            return;
        }

        $this->user->disableTwoFactorAuthentication();
        $this->showingRecoveryCodes = false;

        Audit::log(ActivityEvent::TwoFactorDisabled, $this->user);

        $this->toast(__('Two-factor authentication disabled.'), 'info');
    }

    protected function ensurePasswordIsConfirmed(): bool
    {
        $confirmedAt = (int) session('auth.password_confirmed_at', 0);

        if (time() - $confirmedAt < (int) config('auth.password_timeout', 10800)) {
            return true;
        }

        session()->put('url.intended', route('settings.two-factor'));

        $this->redirectRoute('password.confirm', navigate: true);

        return false;
    }

    public function render(): View
    {
        return view('livewire.settings.two-factor-authentication')->title(__('Two-factor authentication'));
    }
}
