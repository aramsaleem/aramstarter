<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ActivityEvent;
use App\Enums\SocialProvider;
use App\Enums\SystemRole;
use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\Audit;
use App\Support\PendingTwoFactorLogin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as OAuth2User;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class SocialLoginController extends Controller
{
    /**
     * Send the user to the provider's consent screen.
     */
    public function redirect(Request $request, SocialProvider $provider): SymfonyRedirectResponse
    {
        abort_unless($provider->isEnabled(), 404);

        // Connecting a provider adds a permanent way into the account, so a hijacked
        // session alone must not be enough: ask for the password first.
        $user = $request->user();
        $confirmedAt = (int) $request->session()->get('auth.password_confirmed_at', 0);

        if ($user?->hasPassword() && time() - $confirmedAt > (int) config('auth.password_timeout', 10800)) {
            $request->session()->put('url.intended', route('social.redirect', $provider));

            return redirect()->route('password.confirm');
        }

        return Socialite::driver($provider->value)->redirect();
    }

    /**
     * Handle the provider's response: sign in, sign up, or connect the
     * provider to the signed-in user's account.
     */
    public function callback(Request $request, SocialProvider $provider): RedirectResponse
    {
        abort_unless($provider->isEnabled(), 404);

        try {
            $providerUser = Socialite::driver($provider->value)->user();
        } catch (Throwable $e) {
            report($e);

            return $this->failed(__('We could not sign you in with :provider. Please try again.', ['provider' => $provider->label()]));
        }

        $account = SocialAccount::query()
            ->with('user')
            ->where('provider', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if (Auth::check()) {
            return $this->connect($provider, $providerUser, $account);
        }

        $user = $account?->user;

        if (! $user) {
            $email = $this->verifiedEmail($providerUser);

            if (blank($email)) {
                return $this->failed(__(':provider did not share an email address. Please register with your email address instead.', ['provider' => $provider->label()]));
            }

            $user = User::where('email', $email)->first();

            // Only link to accounts that proved they own the address. Otherwise whoever
            // registered it first (without verifying) would share the account.
            if ($user && ! $user->hasVerifiedEmail()) {
                return $this->failed(__('An account with this email address already exists. Log in with your password to connect :provider.', ['provider' => $provider->label()]));
            }

            $user ??= $this->register($providerUser, $email);
        }

        $this->syncAccount($user, $provider, $providerUser);

        if ($user->hasEnabledTwoFactorAuthentication()) {
            PendingTwoFactorLogin::start($user);

            return redirect()->route('two-factor.login');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function connect(SocialProvider $provider, ProviderUser $providerUser, ?SocialAccount $account): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($account && ! $account->user->is($user)) {
            return redirect()->route('settings.connected-accounts')->with('alert', [
                'icon' => 'error',
                'title' => __('This :provider account is already connected to another user.', ['provider' => $provider->label()]),
            ]);
        }

        $this->syncAccount($user, $provider, $providerUser);

        Audit::log(ActivityEvent::SocialConnected, $user, ['provider' => $provider->value]);

        return redirect()->route('settings.connected-accounts')->with('alert', [
            'icon' => 'success',
            'title' => __(':provider account connected.', ['provider' => $provider->label()]),
        ]);
    }

    private function register(ProviderUser $providerUser, string $email): User
    {
        $user = User::create([
            'name' => $providerUser->getName() ?: $providerUser->getNickname() ?: Str::before($email, '@'),
            'email' => $email,
            'locale' => app()->getLocale(),
        ]);

        // The provider has verified the address, and the account has no password until the user sets one.
        $user->markEmailAsVerified();
        $user->assignRole(SystemRole::User->role());

        event(new Registered($user));

        return $user;
    }

    private function syncAccount(User $user, SocialProvider $provider, ProviderUser $providerUser): void
    {
        $user->socialAccounts()->updateOrCreate(['provider' => $provider], [
            'provider_id' => $providerUser->getId(),
            'provider_email' => $providerUser->getEmail(),
            'avatar' => $providerUser->getAvatar(),
            'token' => $providerUser instanceof OAuth2User ? $providerUser->token : null,
            'refresh_token' => $providerUser instanceof OAuth2User ? $providerUser->refreshToken : null,
            'token_expires_at' => $providerUser instanceof OAuth2User && $providerUser->expiresIn
                ? now()->addSeconds((int) $providerUser->expiresIn)
                : null,
        ]);
    }

    /**
     * The provider's email address, or null when the provider says it is not verified.
     * Unverified addresses must never be used to find or create accounts.
     */
    private function verifiedEmail(ProviderUser $providerUser): ?string
    {
        $raw = $providerUser instanceof OAuth2User ? $providerUser->getRaw() : [];
        $verified = $raw['email_verified'] ?? $raw['verified_email'] ?? true;

        if ($verified === false || $verified === 'false') {
            return null;
        }

        return $providerUser->getEmail();
    }

    private function failed(string $message): RedirectResponse
    {
        return redirect()->route('login')->with('error', $message);
    }
}
