<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

final class Localization
{
    /**
     * @return array<string, array{name: string, native: string, dir: string}>
     */
    public static function supported(): array
    {
        return config('localization.supported', []);
    }

    /**
     * Supported locale codes, with the application's default locale first.
     *
     * @return list<string>
     */
    public static function codes(): array
    {
        $codes = array_keys(self::supported());
        $default = config('app.locale');

        if (in_array($default, $codes, true)) {
            $codes = [$default, ...array_values(array_diff($codes, [$default]))];
        }

        return $codes;
    }

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, self::supported());
    }

    public static function direction(?string $locale = null): string
    {
        return self::supported()[$locale ?? App::getLocale()]['dir'] ?? 'ltr';
    }

    public static function isRtl(?string $locale = null): bool
    {
        return self::direction($locale) === 'rtl';
    }

    public static function nativeName(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return self::supported()[$locale]['native'] ?? $locale;
    }
}
