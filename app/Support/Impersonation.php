<?php

namespace App\Support;

use App\Enums\ActivityEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Lets an admin sign in as another user, then return to their own account.
 *
 * Each switch starts a brand-new session, so nothing carries over between the two
 * accounts: no password confirmation, no "intended" URL, no half-finished login.
 * Sessions end on their own after MAX_MINUTES (see HandleImpersonation).
 */
final class Impersonation
{
    public const MAX_MINUTES = 60;

    private const IMPERSONATOR_ID = 'impersonation.impersonator_id';

    private const IMPERSONATOR_EMAIL = 'impersonation.impersonator_email';

    private const STARTED_AT = 'impersonation.started_at';

    /**
     * True while the guard is switching users, so the Login event isn't logged as a normal sign-in.
     */
    private static bool $switching = false;

    public static function active(): bool
    {
        return session()->has(self::IMPERSONATOR_ID);
    }

    public static function switching(): bool
    {
        return self::$switching;
    }

    public static function impersonator(): ?User
    {
        return self::active() ? User::find(session(self::IMPERSONATOR_ID)) : null;
    }

    public static function impersonatorEmail(): ?string
    {
        return session(self::IMPERSONATOR_EMAIL);
    }

    public static function endsAt(): ?Carbon
    {
        return self::active()
            ? Carbon::createFromTimestamp((int) session(self::STARTED_AT))->addMinutes(self::MAX_MINUTES)
            : null;
    }

    public static function expired(): bool
    {
        return self::active() && now()->greaterThanOrEqualTo(self::endsAt());
    }

    /**
     * Sign the impersonator in as the target. Authorization is the caller's job (UserPolicy::impersonate).
     */
    public static function start(User $impersonator, User $target): void
    {
        Audit::log(ActivityEvent::ImpersonationStarted, $target, user: $impersonator);

        self::switchTo($target);

        session()->put([
            self::IMPERSONATOR_ID => $impersonator->getKey(),
            self::IMPERSONATOR_EMAIL => $impersonator->email,
            self::STARTED_AT => now()->getTimestamp(),
        ]);
    }

    /**
     * Go back to the impersonator's own account.
     *
     * @return User|null The user who was being impersonated, or null when the
     *                   impersonator no longer exists and everyone was signed out.
     */
    public static function stop(): ?User
    {
        $target = Auth::user();
        $impersonator = self::impersonator();
        $startedAt = (int) session(self::STARTED_AT);

        if (! $impersonator) {
            Auth::guard('web')->logout();
            session()->invalidate();
            session()->regenerateToken();

            return null;
        }

        if ($target instanceof User) {
            Audit::log(ActivityEvent::ImpersonationEnded, $target, [
                'minutes' => (int) ceil(max(0, now()->getTimestamp() - $startedAt) / 60),
            ], $impersonator);
        }

        self::switchTo($impersonator);

        return $target instanceof User ? $target : null;
    }

    private static function switchTo(User $user): void
    {
        $locale = session('locale');

        // A fresh session for the other account: drops password confirmation and every other trace of the first one.
        session()->invalidate();
        session()->regenerateToken();

        self::$switching = true;

        try {
            Auth::guard('web')->login($user);
        } finally {
            self::$switching = false;
        }

        if ($locale) {
            session()->put('locale', $locale);
        }

        // AuthenticateSession compares this with the signed-in user's password hash on every request.
        $passwordHash = $user->getAuthPassword();

        if ($passwordHash) {
            session()->put('password_hash_web', Auth::guard('web')->hashPasswordForCookie($passwordHash));
        } else {
            session()->forget('password_hash_web');
        }
    }
}
