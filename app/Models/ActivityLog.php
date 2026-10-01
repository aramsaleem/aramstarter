<?php

namespace App\Models;

use App\Enums\ActivityEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An entry in the security audit trail. Entries are append-only and are pruned
 * after RETENTION_DAYS by the scheduled model:prune command.
 */
class ActivityLog extends Model
{
    use MassPrunable;

    public const RETENTION_DAYS = 180;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'event',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event' => ActivityEvent::class,
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A readable name for what the event was about, captured when it happened,
     * so it stays meaningful after the subject is deleted.
     */
    public function subjectLabel(): ?string
    {
        return $this->properties['label'] ?? null;
    }

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(self::RETENTION_DAYS));
    }
}
