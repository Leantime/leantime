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

    /** Between editor and manager: can run team 1:1 flows like a line lead. */
    public static string $teamlead = 'teamlead';

    public static string $manager = 'manager';

    public static string $admin = 'admin';

    public static string $owner = 'owner';

    private static array $roleKeys = [
        5 => 'readonly',      // prev: none
        10 => 'commenter',    // prev: client
        20 => 'editor',       // prev: developer
        25 => 'teamlead',     // IntelliVerse: team lead (1:1 team view, between editor & manager)
        30 => 'manager',      // prev: clientmanager
        40 => 'admin',        // prev: manager
        50 => 'owner',        // prev: admin
    ];

    /**
     * Roles hidden from the UI assignment dropdown.
     * readonly: too restrictive, no use case. owner: merged into admin.
     */
    private static array $hiddenRoles = ['readonly', 'owner'];

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
     * Returns only the roles that should appear in user-facing dropdowns.
     * Excludes readonly (too restrictive) and owner (merged into admin).
     * Display order: Client, Developer, Team Lead, Manager, Administrator.
     *
     * @throws BindingResolutionException
     */
    public static function getAssignableRoles(): array
    {
        return array_filter(
            self::getFilteredRoles(),
            static fn (string $role): bool => ! in_array($role, self::$hiddenRoles, true)
        );
    }
}
