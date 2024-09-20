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
        5  => 'readonly',      //prev: none
        10 => 'commenter',    //prev: client
        20 => 'editor',       //prev: developer
        30 => 'manager',      //prev: clientmanager
        40 => 'admin',        //prev: manager
        50 => 'owner',        //prev: admin
    ];

    /**
     * @throws BindingResolutionException
     *
     * @return mixed
     */
    private static function getFilteredRoles(): mixed
    {
        return self::dispatch_filter('available_roles', self::$roleKeys);
    }

    /**
     * @param mixed $key
     *
     * @throws BindingResolutionException
     *
     * @return false|mixed
     */
    public static function getRoleString(mixed $key): mixed
    {
        return self::getFilteredRoles()[$key] ?? false;
    }

    /**
     * @throws BindingResolutionException
     *
     * @return mixed
     */
    public static function getRoles(): mixed
    {
        return self::getFilteredRoles();
    }
}
