<?php

namespace App\Enums;

use Illuminate\Support\Str;

/**
 * Everything the audit trail records. Values are stored in activity_logs.event.
 */
enum ActivityEvent: string
{
    case Login = 'auth.login';
    case LoginFailed = 'auth.failed';
    case Logout = 'auth.logout';
    case Lockout = 'auth.lockout';
    case Registered = 'auth.registered';
    case EmailVerified = 'auth.verified';
    case PasswordReset = 'auth.password_reset';

    case ProfileUpdated = 'account.profile_updated';
    case PasswordChanged = 'account.password_changed';
    case AccountDeleted = 'account.deleted';

    case TwoFactorEnabled = '2fa.enabled';
    case TwoFactorDisabled = '2fa.disabled';
    case TwoFactorFailed = '2fa.failed';
    case RecoveryCodeUsed = '2fa.recovery_code_used';
    case RecoveryCodesRegenerated = '2fa.recovery_codes_regenerated';

    case SocialConnected = 'social.connected';
    case SocialDisconnected = 'social.disconnected';

    case UserCreated = 'user.created';
    case UserUpdated = 'user.updated';
    case UserDeleted = 'user.deleted';
    case UserTwoFactorReset = 'user.2fa_reset';
    case ImpersonationStarted = 'user.impersonation_started';
    case ImpersonationEnded = 'user.impersonation_ended';

    case RoleCreated = 'role.created';
    case RoleUpdated = 'role.updated';
    case RoleDeleted = 'role.deleted';

    case PermissionCreated = 'permission.created';
    case PermissionUpdated = 'permission.updated';
    case PermissionDeleted = 'permission.deleted';

    case ContentUpdated = 'content.updated';

    public function label(): string
    {
        return match ($this) {
            self::Login => __('Signed in'),
            self::LoginFailed => __('Failed sign-in attempt'),
            self::Logout => __('Signed out'),
            self::Lockout => __('Too many sign-in attempts'),
            self::Registered => __('Account created'),
            self::EmailVerified => __('Email verified'),
            self::PasswordReset => __('Password reset'),
            self::ProfileUpdated => __('Profile updated'),
            self::PasswordChanged => __('Password changed'),
            self::AccountDeleted => __('Account deleted'),
            self::TwoFactorEnabled => __('Two-factor enabled'),
            self::TwoFactorDisabled => __('Two-factor disabled'),
            self::TwoFactorFailed => __('Wrong two-factor code'),
            self::RecoveryCodeUsed => __('Recovery code used'),
            self::RecoveryCodesRegenerated => __('Recovery codes regenerated'),
            self::SocialConnected => __('Social account connected'),
            self::SocialDisconnected => __('Social account disconnected'),
            self::UserCreated => __('User created'),
            self::UserUpdated => __('User updated'),
            self::UserDeleted => __('User deleted'),
            self::UserTwoFactorReset => __('Two-factor reset by an admin'),
            self::ImpersonationStarted => __('Signed in as another user'),
            self::ImpersonationEnded => __('Returned from another user'),
            self::RoleCreated => __('Role created'),
            self::RoleUpdated => __('Role updated'),
            self::RoleDeleted => __('Role deleted'),
            self::PermissionCreated => __('Permission created'),
            self::PermissionUpdated => __('Permission updated'),
            self::PermissionDeleted => __('Permission deleted'),
            self::ContentUpdated => __('Website content updated'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Login, self::Registered => 'arrow-right-end-on-rectangle',
            self::Logout => 'arrow-right-start-on-rectangle',
            self::LoginFailed, self::TwoFactorFailed => 'exclamation-triangle',
            self::Lockout => 'no-symbol',
            self::EmailVerified => 'check-badge',
            self::PasswordReset, self::PasswordChanged => 'key',
            self::ProfileUpdated => 'user-circle',
            self::AccountDeleted, self::UserDeleted, self::RoleDeleted, self::PermissionDeleted => 'trash',
            self::TwoFactorEnabled, self::TwoFactorDisabled, self::UserTwoFactorReset => 'finger-print',
            self::RecoveryCodeUsed, self::RecoveryCodesRegenerated => 'lifebuoy',
            self::SocialConnected, self::SocialDisconnected => 'link',
            self::UserCreated, self::UserUpdated => 'users',
            self::RoleCreated, self::RoleUpdated => 'identification',
            self::PermissionCreated, self::PermissionUpdated => 'key',
            self::ContentUpdated => 'globe-alt',
            self::ImpersonationStarted => 'eye',
            self::ImpersonationEnded => 'eye-slash',
        };
    }

    /**
     * @return 'info'|'success'|'warning'|'danger'
     */
    public function severity(): string
    {
        return match ($this) {
            self::LoginFailed, self::TwoFactorFailed, self::Lockout => 'danger',
            self::TwoFactorDisabled, self::UserTwoFactorReset, self::RecoveryCodeUsed, self::ImpersonationStarted,
            self::AccountDeleted, self::UserDeleted, self::RoleDeleted, self::PermissionDeleted,
            self::SocialDisconnected => 'warning',
            self::TwoFactorEnabled, self::EmailVerified, self::Registered => 'success',
            default => 'info',
        };
    }

    /**
     * @return 'auth'|'account'|'admin'|'content'
     */
    public function group(): string
    {
        return match (Str::before($this->value, '.')) {
            'auth' => 'auth',
            'account', '2fa', 'social' => 'account',
            'content' => 'content',
            default => 'admin',
        };
    }

    /**
     * @return array<string, string> Group key => translated label.
     */
    public static function groups(): array
    {
        return [
            'auth' => __('Sign-ins'),
            'account' => __('Account security'),
            'admin' => __('Admin changes'),
            'content' => __('Website'),
        ];
    }

    /**
     * @return list<self>
     */
    public static function inGroup(string $group): array
    {
        return array_values(array_filter(self::cases(), fn (self $event) => $event->group() === $group));
    }

    /**
     * @return list<self>
     */
    public static function withSeverity(string $severity): array
    {
        return array_values(array_filter(self::cases(), fn (self $event) => $event->severity() === $severity));
    }

    /**
     * Events that point at a possible attack.
     *
     * @return list<self>
     */
    public static function threats(): array
    {
        return [self::LoginFailed, self::TwoFactorFailed, self::Lockout];
    }
}
