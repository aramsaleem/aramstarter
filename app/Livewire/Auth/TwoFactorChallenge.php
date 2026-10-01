<?php

namespace App\Livewire\Auth;

use App\Enums\ActivityEvent;
use App\Services\TwoFactorAuthenticator;
use App\Support\Audit;
use App\Support\PendingTwoFactorLogin;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class TwoFactorChallenge extends Component
{
    public string $code = '';

    public string $recovery_code = '';

    public bool $usingRecoveryCode = false;

    public function mount(): void
    {
        if (! PendingTwoFactorLogin::user()?->hasEnabledTwoFactorAuthentication()) {
            PendingTwoFactorLogin::clear();

            $this->redirectRoute('login', navigate: true);
        }
    }

    public function toggleRecoveryCode(): void
    {
        $this->usingRecoveryCode = ! $this->usingRecoveryCode;

        $this->reset('code', 'recovery_code');
        $this->resetErrorBag();
    }

    /**
     * Finish logging in with a code from the authenticator app or a recovery code.
     */
    public function authenticate(TwoFactorAuthenticator $authenticator): void
    {
        $user = PendingTwoFactorLogin::user();

        if (! $user?->hasEnabledTwoFactorAuthentication()) {
            PendingTwoFactorLogin::clear();

            $this->redirectRoute('login', navigate: true);

            return;
        }

        $field = $this->usingRecoveryCode ? 'recovery_code' : 'code';

        $this->validate([$field => ['required', 'string']]);

        // Per account, not per IP: rotating IP addresses must not buy extra guesses.
        $throttleKey = 'two-factor:'.$user->getKey();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                $field => __('auth.throttle', ['seconds' => RateLimiter::availableIn($throttleKey)]),
            ]);
        }

        $valid = $this->usingRecoveryCode
            ? $user->useRecoveryCode($this->recovery_code)
            : $authenticator->verify($user, $this->code);

        if (! $valid) {
            RateLimiter::hit($throttleKey);
            $this->reset($field);

            Audit::log(ActivityEvent::TwoFactorFailed, $user, ['method' => $field], $user);

            throw ValidationException::withMessages([
                $field => $this->usingRecoveryCode
                    ? __('The recovery code is invalid.')
                    : __('The authentication code is invalid.'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        if ($this->usingRecoveryCode) {
            Audit::log(ActivityEvent::RecoveryCodeUsed, $user, user: $user);
        }

        $remember = PendingTwoFactorLogin::remember();
        PendingTwoFactorLogin::clear();

        Auth::login($user, $remember);
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.two-factor-challenge')->title(__('Two-factor authentication'));
    }
}
