<?php

namespace App\Models;

use App\Enums\SystemRole;
use App\Models\Concerns\TwoFactorAuthenticatable;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<SocialAccount, $this>
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get the user's initials, e.g. "Jane Doe" becomes "JD".
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');
    }

    /**
     * Users created through social login have no password until they set one.
     */
    public function hasPassword(): bool
    {
        return $this->password !== null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(SystemRole::SuperAdmin);
    }

    /**
     * Whether this user may hand out the given permissions. Super Admins may grant anything;
     * everyone else only permissions they hold themselves, so nobody can escalate their own
     * privileges (or anyone else's) beyond their own level.
     *
     * @param  iterable<string>  $permissions
     */
    public function canGrantPermissions(iterable $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return collect($permissions)
            ->diff($this->getAllPermissions()->pluck('name'))
            ->isEmpty();
    }

    /**
     * Whether this user is at least as powerful as the other one: a Super Admin,
     * or someone who holds every permission the other user has.
     */
    public function outranks(User $other): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return ! $other->isSuperAdmin()
            && $this->canGrantPermissions($other->getAllPermissions()->pluck('name'));
    }

    /**
     * Mail and notifications are sent in the user's chosen language.
     */
    public function preferredLocale(): ?string
    {
        return $this->locale;
    }
}
