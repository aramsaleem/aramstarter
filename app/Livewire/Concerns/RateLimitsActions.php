<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Livewire actions run through /livewire/update, so route "throttle" middleware
 * doesn't protect them. Anything that sends email or can be brute-forced calls this.
 */
trait RateLimitsActions
{
    /**
     * Count one attempt, failing validation on $field once the limit is reached.
     */
    protected function rateLimit(string $key, int $maxAttempts, int $decaySeconds, string $field): void
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                $field => __('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);
    }
}
