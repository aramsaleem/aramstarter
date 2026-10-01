<?php

namespace App\Enums;

enum SocialProvider: string
{
    case Google = 'google';
    case Facebook = 'facebook';
    case X = 'x';

    public function label(): string
    {
        return match ($this) {
            self::Google => 'Google',
            self::Facebook => 'Facebook',
            self::X => 'X',
        };
    }

    /**
     * A provider is only offered once its OAuth credentials are configured in config/services.php.
     */
    public function isEnabled(): bool
    {
        return filled(config("services.{$this->value}.client_id"))
            && filled(config("services.{$this->value}.client_secret"));
    }

    /**
     * @return list<self>
     */
    public static function enabled(): array
    {
        return array_values(array_filter(self::cases(), fn (self $provider) => $provider->isEnabled()));
    }
}
