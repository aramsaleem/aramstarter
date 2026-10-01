<?php

namespace App\Support;

/**
 * A short, readable description of a browser's user agent string, e.g. "Chrome · Windows".
 * Good enough for an audit log; not meant for feature detection.
 */
final class UserAgent
{
    private const BROWSERS = [
        'Edg/' => 'Edge',
        'OPR/' => 'Opera',
        'SamsungBrowser/' => 'Samsung Internet',
        'Firefox/' => 'Firefox',
        'FxiOS/' => 'Firefox',
        'CriOS/' => 'Chrome',
        'Chrome/' => 'Chrome',
        'Safari/' => 'Safari',
        'curl/' => 'curl',
    ];

    private const PLATFORMS = [
        'Windows' => 'Windows',
        'iPhone' => 'iOS',
        'iPad' => 'iPadOS',
        'Android' => 'Android',
        'Mac OS X' => 'macOS',
        'CrOS' => 'ChromeOS',
        'Linux' => 'Linux',
    ];

    public static function describe(?string $userAgent): ?string
    {
        if (blank($userAgent)) {
            return null;
        }

        $browser = self::match($userAgent, self::BROWSERS);
        $platform = self::match($userAgent, self::PLATFORMS);

        return implode(' · ', array_filter([$browser, $platform])) ?: null;
    }

    public static function isMobile(?string $userAgent): bool
    {
        return (bool) preg_match('/Mobile|iPhone|Android/i', (string) $userAgent);
    }

    /**
     * @param  array<string, string>  $needles
     */
    private static function match(string $userAgent, array $needles): ?string
    {
        foreach ($needles as $needle => $name) {
            if (str_contains($userAgent, $needle)) {
                return $name;
            }
        }

        return null;
    }
}
