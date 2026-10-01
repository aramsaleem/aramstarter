<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Permissions use "group.action" names, e.g. "users.create".
 */
final class PermissionLabel
{
    public static function group(string $permission): string
    {
        return str_contains($permission, '.') ? Str::before($permission, '.') : 'other';
    }

    public static function action(string $permission): string
    {
        return str_contains($permission, '.') ? Str::after($permission, '.') : $permission;
    }

    /**
     * Human readable, translated group name, e.g. "users" => "Users".
     */
    public static function groupLabel(string $group): string
    {
        return __(Str::headline($group));
    }

    /**
     * Human readable, translated action name, e.g. "users.create" => "Create".
     */
    public static function actionLabel(string $permission): string
    {
        return __(Str::headline(self::action($permission)));
    }
}
