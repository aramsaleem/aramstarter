<?php

namespace Database\Factories;

use App\Enums\SystemRole;
use App\Models\User;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'locale' => null,
            // Listed explicitly so factory models carry every column, like users loaded from the
            // database do - strict models throw when a cast attribute was never loaded.
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * An account created through social login that never set a password.
     */
    public function withoutPassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
        ]);
    }

    /**
     * A user with confirmed two-factor authentication.
     */
    public function withTwoFactor(): static
    {
        return $this->state(function (array $attributes) {
            $authenticator = app(TwoFactorAuthenticator::class);

            return [
                'two_factor_secret' => $authenticator->generateSecretKey(),
                'two_factor_recovery_codes' => $authenticator->generateRecoveryCodes(),
                'two_factor_confirmed_at' => now(),
            ];
        });
    }

    public function withRole(SystemRole $role): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole($role->role()));
    }

    public function superAdmin(): static
    {
        return $this->withRole(SystemRole::SuperAdmin);
    }

    public function admin(): static
    {
        return $this->withRole(SystemRole::Admin);
    }
}
