<?php

namespace Database\Factories;

use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialAccount>
 */
class SocialAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => SocialProvider::Google,
            'provider_id' => (string) fake()->unique()->numberBetween(100_000, 999_999_999),
            'provider_email' => fake()->safeEmail(),
            'avatar' => null,
            'token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ];
    }

    public function provider(SocialProvider $provider): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => $provider,
        ]);
    }
}
