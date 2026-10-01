<?php

namespace App\Support;

use App\Enums\ActivityEvent;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Writes to the security audit trail.
 *
 * Never pass secrets in $properties: no passwords, codes, tokens or keys.
 */
final class Audit
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function log(ActivityEvent $event, ?Model $subject = null, array $properties = [], ?User $user = null): ActivityLog
    {
        $request = request();

        if ($subject && ! array_key_exists('label', $properties)) {
            $properties['label'] = self::labelFor($subject);
        }

        // Actions taken while signed in as someone else are traced back to the admin who did them.
        if (Impersonation::active() && ($user === null || $user->is(auth()->user()))) {
            $properties['impersonated_by'] = Impersonation::impersonatorEmail();
        }

        return ActivityLog::create([
            'user_id' => ($user ?? auth()->user())?->getKey(),
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);
    }

    private static function labelFor(Model $subject): string
    {
        return match (true) {
            $subject instanceof User => $subject->email,
            // Read the raw attribute: strict models throw on columns a model doesn't have.
            is_string($subject->getAttributes()['name'] ?? null) => $subject->getAttributes()['name'],
            default => class_basename($subject).' #'.$subject->getKey(),
        };
    }
}
