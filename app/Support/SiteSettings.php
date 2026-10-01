<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Editable website settings (Admin > Website > Content).
 *
 * Every setting has an English default. Defaults are passed through __(), so the site
 * is fully translated before anyone edits anything. Stored values override them.
 */
final class SiteSettings
{
    private const CACHE_KEY = 'site-settings';

    /**
     * @return array<string, array{group: string, type: 'text'|'textarea'|'email'|'boolean', translatable: bool, default: mixed, max?: int}>
     */
    public static function fields(): array
    {
        $tagline = 'Authentication, two-factor security, social login, roles and permissions, localization and an admin panel - ready to build on.';

        return [
            'hero_badge' => ['group' => 'hero', 'type' => 'text', 'translatable' => true, 'default' => 'Laravel 12 starter kit, ready for production', 'max' => 80],
            'hero_title' => ['group' => 'hero', 'type' => 'text', 'translatable' => true, 'default' => 'Launch your SaaS', 'max' => 60],
            'hero_highlight' => ['group' => 'hero', 'type' => 'text', 'translatable' => true, 'default' => 'in days, not months', 'max' => 60],
            'hero_subtitle' => ['group' => 'hero', 'type' => 'textarea', 'translatable' => true, 'default' => $tagline, 'max' => 300],
            'hero_primary_cta' => ['group' => 'hero', 'type' => 'text', 'translatable' => true, 'default' => 'Start for free', 'max' => 30],
            'hero_secondary_cta' => ['group' => 'hero', 'type' => 'text', 'translatable' => true, 'default' => 'See how it works', 'max' => 30],

            'cta_title' => ['group' => 'cta', 'type' => 'text', 'translatable' => true, 'default' => 'Ship your idea this week, not next quarter', 'max' => 90],
            'cta_subtitle' => ['group' => 'cta', 'type' => 'textarea', 'translatable' => true, 'default' => 'Create your account in seconds and explore the dashboard yourself.', 'max' => 200],

            'footer_tagline' => ['group' => 'general', 'type' => 'textarea', 'translatable' => true, 'default' => $tagline, 'max' => 300],
            'contact_email' => ['group' => 'general', 'type' => 'email', 'translatable' => false, 'default' => null, 'max' => 255],

            'show_stats' => ['group' => 'sections', 'type' => 'boolean', 'translatable' => false, 'default' => true],
            'show_features' => ['group' => 'sections', 'type' => 'boolean', 'translatable' => false, 'default' => true],
            'show_how_it_works' => ['group' => 'sections', 'type' => 'boolean', 'translatable' => false, 'default' => true],
            'show_pricing' => ['group' => 'sections', 'type' => 'boolean', 'translatable' => false, 'default' => true],
            'show_faq' => ['group' => 'sections', 'type' => 'boolean', 'translatable' => false, 'default' => true],
        ];
    }

    /**
     * Every setting resolved for one language.
     *
     * @return array<string, mixed>
     */
    public static function for(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $stored = self::stored();
        $resolved = [];

        foreach (self::fields() as $key => $field) {
            $value = $stored[$key] ?? null;

            if ($field['translatable']) {
                $value = is_array($value) ? array_filter($value, fn ($text) => filled($text)) : [];
                $fallback = config('app.fallback_locale');

                // Edited text wins; untouched settings use the translated default.
                $resolved[$key] = $value[$locale]
                    ?? $value[$fallback]
                    ?? ($field['default'] === null ? null : __($field['default'], [], $locale));
            } else {
                $resolved[$key] = $value ?? $field['default'];
            }
        }

        return $resolved;
    }

    /**
     * Stored values, as the admin form edits them (translations as arrays).
     *
     * @return array<string, mixed>
     */
    public static function stored(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            if (! Schema::hasTable('site_settings')) {
                return [];
            }

            return SiteSetting::pluck('value', 'key')->all();
        });
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function save(array $values): void
    {
        foreach (array_intersect_key($values, self::fields()) as $key => $value) {
            // An empty value means "use the default", so there's nothing to keep.
            if ($value === null || $value === []) {
                SiteSetting::where('key', $key)->delete();
            } else {
                SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Go back to the default text for everything.
     */
    public static function reset(): void
    {
        SiteSetting::query()->delete();

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * The default text of every translatable setting, per language (used as placeholders).
     *
     * @return array<string, array<string, string>>
     */
    public static function defaults(): array
    {
        $defaults = [];

        foreach (self::fields() as $key => $field) {
            if ($field['translatable'] && is_string($field['default'])) {
                foreach (Localization::codes() as $locale) {
                    $defaults[$key][$locale] = __($field['default'], [], $locale);
                }
            }
        }

        return $defaults;
    }
}
