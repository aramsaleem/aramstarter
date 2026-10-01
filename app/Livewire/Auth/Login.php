<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Support\PendingTwoFactorLogin;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        $user = $this->validateCredentials();

        RateLimiter::clear($this->throttleKey());

        if ($user->hasEnabledTwoFactorAuthentication()) {
            PendingTwoFactorLogin::start($user, $this->remember);

            $this->redirectRoute('two-factor.login', navigate: true);

            return;
        }

        Auth::login($user, $this->remember);
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Check the credentials without logging in, so users with 2FA can be sent to the challenge first.
     */
    protected function validateCredentials(): User
    {
        $credentials = ['email' => $this->email, 'password' => $this->password];
        $provider = Auth::getProvider();

        // Unknown emails skip the slow password hash check. A time box makes failures take
        // the same time either way, so response times can't reveal which accounts exist.
        $user = (new Timebox)->call(function (Timebox $timebox) use ($provider, $credentials) {
            $user = $provider->retrieveByCredentials($credentials);

            if ($user instanceof User && $provider->validateCredentials($user, $credentials)) {
                $timebox->returnEarly();

                return $user;
            }

            return null;
        }, 400_000);

        if (! $user) {
            event(new Failed('web', null, $credentials));

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $provider->rehashPasswordIfRequired($user, $credentials);

        return $user;
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function render(): View
    {
        return view('livewire.auth.login')->title(__('Log in'));
    }
}
