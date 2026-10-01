<?php

namespace App\Models\Concerns;

use App\Services\TwoFactorAuthenticator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string|null $two_factor_secret
 * @property list<string>|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 */
trait TwoFactorAuthenticatable
{
    /**
     * Two-factor authentication only counts as enabled once the user confirmed a code from their app.
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    /**
     * @return list<string>
     */
    public function recoveryCodes(): array
    {
        return $this->two_factor_recovery_codes ?? [];
    }

    /**
     * Consume a recovery code so it can't be used again.
     */
    public function useRecoveryCode(string $code): bool
    {
        $code = Str::lower(trim($code));
        $codes = $this->recoveryCodes();

        foreach ($codes as $index => $candidate) {
            if (hash_equals($candidate, $code)) {
                unset($codes[$index]);

                $this->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }

    public function twoFactorQrCodeSvg(): string
    {
        return app(TwoFactorAuthenticator::class)->qrCodeSvg($this);
    }

    public function disableTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }
}
