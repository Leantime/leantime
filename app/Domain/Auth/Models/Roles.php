<?php

namespace Leantime\Domain\Auth\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Events\DispatchesEvents;

/**
 * @TODO: Role names should be converted into an enum.
 */
class Roles
{
    use DispatchesEvents;

    public static string $readonly = 'readonly';

    public static string $commenter = 'commenter';

    public static string $editor = 'editor';

    public static string $manager = 'manager';

    public static string $admin = 'admin';

    public static string $owner = 'owner';

    private static array $roleKeys = [
        5 => 'readonly',      // prev: none
        10 => 'commenter',    // prev: client
        20 => 'editor',       // prev: developer
        30 => 'manager',      // prev: clientmanager
        40 => 'admin',        // prev: manager
        50 => 'owner',        // prev: admin
    ];

    /**
     * @throws BindingResolutionException
     */
    private static function getFilteredRoles(): mixed
    {
        return self::dispatch_filter('available_roles', self::$roleKeys);
    }

    /**
     * @return false|mixed
     *
     * @throws BindingResolutionException
     */
    public static function getRoleString(mixed $key): mixed
    {
        return self::getFilteredRoles()[$key] ?? false;
    }

    /**
     * @throws BindingResolutionException
     */
    public static function getRoles(): mixed
    {
        return self::getFilteredRoles();
    }

    /**
     * Hierarchy level of a role given either as its numeric key (50, '50') or its name ('owner').
     * Higher is more privileged. Returns false for an unknown role.
     *
     * @param  mixed  $role  Role key or role name.
     *
     * @throws BindingResolutionException
     */
    public static function getRoleLevel(mixed $role): int|false
    {
        $roles = self::getFilteredRoles();

        if (is_int($role) || (is_string($role) && ctype_digit($role))) {
            return isset($roles[(int) $role]) ? (int) $role : false;
        }

        if (! is_string($role) || $role === '') {
            return false;
        }

        $level = array_search($role, $roles, true);

        return $level === false ? false : (int) $level;
    }
}
