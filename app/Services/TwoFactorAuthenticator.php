<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP (RFC 6238) two-factor authentication backed by pragmarx/google2fa.
 * Works with Google Authenticator, Authy, 1Password, Microsoft Authenticator, etc.
 */
class TwoFactorAuthenticator
{
    /** Number of 30 second time steps accepted either side of "now" to tolerate clock drift. */
    public const WINDOW = 1;

    public const RECOVERY_CODE_COUNT = 8;

    public function __construct(
        private readonly Google2FA $engine,
        private readonly Cache $cache,
    ) {}

    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey(32);
    }

    /**
     * @return list<string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODE_COUNT))
            ->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))
            ->all();
    }

    public function qrCodeUrl(User $user): string
    {
        return $this->engine->getQRCodeUrl(
            config('app.name'),
            $user->email,
            (string) $user->two_factor_secret,
        );
    }

    public function qrCodeSvg(User $user): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(192, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(24, 24, 27))),
                new SvgImageBackEnd,
            )
        ))->writeString($this->qrCodeUrl($user));

        // Drop the XML declaration so the SVG can be inlined in HTML.
        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    /**
     * Verify a code from the user's authenticator app. A code is rejected when its
     * time step is not newer than the last accepted one, which prevents replays.
     */
    public function verify(User $user, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';
        $secret = $user->two_factor_secret;

        if ($secret === null || ! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $cacheKey = 'two-factor:last-timestep:'.$user->getKey();

        $timestep = $this->engine->verifyKeyNewer(
            $secret,
            $code,
            (int) $this->cache->get($cacheKey, 0),
            self::WINDOW,
        );

        if ($timestep === false) {
            return false;
        }

        $this->cache->put($cacheKey, $timestep, now()->addMinutes(5));

        return true;
    }
}
