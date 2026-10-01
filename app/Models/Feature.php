<?php

namespace App\Models;

use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * A feature card on the public website.
 */
class Feature extends Model
{
    use HasTranslations, Sortable;

    /**
     * Icons an admin can pick from. Anything else is rejected by validation.
     */
    public const ICONS = [
        'finger-print', 'shield-check', 'lock-closed', 'key', 'language', 'globe-alt', 'identification', 'users',
        'command-line', 'link', 'chart-bar-square', 'chart-pie', 'bolt', 'sparkles', 'rocket-launch', 'cloud',
        'cpu-chip', 'server-stack', 'bell', 'envelope', 'device-phone-mobile', 'puzzle-piece', 'cog-6-tooth',
    ];

    /**
     * Small animated illustrations a card can show (resources/views/landing/visuals).
     */
    public const VISUALS = ['two-factor', 'languages', 'permissions', 'command-palette', 'social', 'analytics'];

    /**
     * @var list<string>
     */
    public array $translatable = ['title', 'description'];

    /**
     * @var list<string>
     */
    protected $fillable = ['icon', 'visual', 'title', 'description', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public static function visualLabel(string $visual): string
    {
        return match ($visual) {
            'two-factor' => __('Two-factor code'),
            'languages' => __('Rotating greetings'),
            'permissions' => __('Permission switches'),
            'command-palette' => __('Keyboard shortcut'),
            'social' => __('Social icons'),
            'analytics' => __('Mini chart'),
            default => $visual,
        };
    }
}
