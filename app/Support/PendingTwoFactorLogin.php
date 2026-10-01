<?php

namespace App\Support;

use App\Models\User;

/**
 * Holds a user who passed the first login step (password or social provider)
 * but still has to answer the two-factor challenge.
 *
 * The pending login expires after a few minutes, which caps how long someone
 * who knows the password can keep guessing codes.
 */
final class PendingTwoFactorLogin
{
    public const LIFETIME_SECONDS = 300;

    private const USER_KEY = 'login.id';

    private const REMEMBER_KEY = 'login.remember';

    private const EXPIRES_KEY = 'login.expires_at';

    public static function start(User $user, bool $remember = false): void
    {
        session()->put([
            self::USER_KEY => $user->getKey(),
            self::REMEMBER_KEY => $remember,
            self::EXPIRES_KEY => now()->addSeconds(self::LIFETIME_SECONDS)->getTimestamp(),
        ]);
    }

    public static function user(): ?User
    {
        $id = session(self::USER_KEY);

        if (! $id || now()->getTimestamp() > (int) session(self::EXPIRES_KEY, 0)) {
            return null;
        }

        return User::find($id);
    }

    public static function remember(): bool
    {
        return (bool) session(self::REMEMBER_KEY, false);
    }

    public static function clear(): void
    {
        session()->forget([self::USER_KEY, self::REMEMBER_KEY, self::EXPIRES_KEY]);
    }
}
