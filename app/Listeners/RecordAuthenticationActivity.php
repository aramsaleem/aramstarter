<?php

namespace App\Listeners;

use App\Enums\ActivityEvent;
use App\Models\User;
use App\Support\Audit;
use App\Support\Impersonation;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Str;

/**
 * Writes authentication events to the audit trail.
 */
class RecordAuthenticationActivity
{
    public function handle(object $event): void
    {
        // Switching accounts for impersonation is recorded by Impersonation itself.
        if (Impersonation::switching()) {
            return;
        }

        match (true) {
            $event instanceof Login => $this->record(ActivityEvent::Login, $event->user, ['remember' => $event->remember]),
            $event instanceof Logout => $this->record(ActivityEvent::Logout, $event->user),
            $event instanceof Registered => $this->record(ActivityEvent::Registered, $event->user),
            $event instanceof Verified => $this->record(ActivityEvent::EmailVerified, $event->user),
            $event instanceof PasswordReset => $this->record(ActivityEvent::PasswordReset, $event->user),
            // Only the attempted email is kept - never the password.
            $event instanceof Failed => Audit::log(ActivityEvent::LoginFailed, $event->user instanceof User ? $event->user : null, [
                'email' => Str::limit((string) ($event->credentials['email'] ?? ''), 255, ''),
            ], $event->user instanceof User ? $event->user : null),
            $event instanceof Lockout => Audit::log(ActivityEvent::Lockout),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function record(ActivityEvent $event, mixed $user, array $properties = []): void
    {
        if ($user instanceof User) {
            Audit::log($event, $user, $properties, $user);
        }
    }
}
