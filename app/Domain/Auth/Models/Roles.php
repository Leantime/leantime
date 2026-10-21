<?php

namespace Leantime\Domain\Auth\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Events\DispatchesEvents;

/**
 * The roles a user can hold, read from `zp_roles`.
 *
 * A role has three things that used to be one number:
 * - its KEY: what `zp_user.role` / `zp_relationuserproject.projectRole` store. Built-ins keep
 *   5..50; custom roles get keys from 100 up.
 * - its LEVEL: its rank, used by every "at least" check. Built-ins: level = key. A custom role
 *   usually takes its base role's level.
 * - its BASE ROLE: the built-in role it acts as wherever code still checks a role by name
 *   (`userHasRole`, `authOrRedirect`). Built-ins are their own base.
 *
 * Without a usable `zp_roles` table (install, before the migration ran, unit tests) the six
 * built-in roles below are used.
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

    private const CACHE_KEY = 'leantime.roleTable';

    private static array $roleKeys = [
        5 => 'readonly',      // prev: none
        10 => 'commenter',    // prev: client
        20 => 'editor',       // prev: developer
        30 => 'manager',      // prev: clientmanager
        40 => 'admin',        // prev: manager
        50 => 'owner',        // prev: admin
    ];

    /**
     * Role table per tenant for this process: [domainKey => [roleKey => role row]].
     */
    private static array $loadedRoleTables = [];

    /**
     * @throws BindingResolutionException
     */
    private static function getFilteredRoles(): mixed
    {
        $roleNamesByKey = array_map(fn (array $role) => $role['name'], self::roleTable());

        return self::dispatch_filter('available_roles', $roleNamesByKey);
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
     * All roles as [roleKey => roleName], lowest level first.
     *
     * @throws BindingResolutionException
     */
    public static function getRoles(): mixed
    {
        return self::getFilteredRoles();
    }

    /**
     * Stored key of a role given either as its key (50, '50') or its name ('owner').
     * Returns false for an unknown role.
     *
     * @param  mixed  $role  Role key or role name.
     *
     * @throws BindingResolutionException
     */
    public static function getRoleKey(mixed $role): int|false
    {
        $roles = self::getFilteredRoles();

        if (is_int($role) || (is_string($role) && ctype_digit($role))) {
            return isset($roles[(int) $role]) ? (int) $role : false;
        }

        if (! is_string($role) || $role === '') {
            return false;
        }

        $roleKey = array_search($role, $roles, true);

        return $roleKey === false ? false : (int) $roleKey;
    }

    /**
     * Rank of a role given either as its key or its name. Higher is more privileged.
     * Returns false for an unknown role.
     *
     * @param  mixed  $role  Role key or role name.
     *
     * @throws BindingResolutionException
     */
    public static function getRoleLevel(mixed $role): int|false
    {
        $roleKey = self::getRoleKey($role);

        if ($roleKey === false) {
            return false;
        }

        // Roles a plugin adds through the `available_roles` filter have no table row: their key
        // is their level, as before.
        return self::roleTable()[$roleKey]['level'] ?? $roleKey;
    }

    /**
     * Whether a role ranks at or above another one. False when either role is unknown.
     *
     * @param  mixed  $role  Role key or name to test.
     * @param  mixed  $requiredRole  Role key or name it has to reach.
     *
     * @throws BindingResolutionException
     */
    public static function isAtLeast(mixed $role, mixed $requiredRole): bool
    {
        $roleLevel = self::getRoleLevel($role);
        $requiredLevel = self::getRoleLevel($requiredRole);

        return $roleLevel !== false && $requiredLevel !== false && $roleLevel >= $requiredLevel;
    }

    /**
     * Keys of every role ranked at or above the given one, as strings because that is how
     * `zp_user.role` stores them. For `whereIn` / `in_array` on stored role values.
     *
     * @param  mixed  $requiredRole  Role key or name.
     * @return string[]
     *
     * @throws BindingResolutionException
     */
    public static function keysAtLeast(mixed $requiredRole): array
    {
        $matchingKeys = [];

        foreach (array_keys(self::getFilteredRoles()) as $roleKey) {
            if (self::isAtLeast($roleKey, $requiredRole)) {
                $matchingKeys[] = (string) $roleKey;
            }
        }

        return $matchingKeys;
    }

    /**
     * Whether a role sees every project without being a member (it holds `projects.viewAll`).
     * Falls back to "admin and above" when the grant map can't be read (no database).
     *
     * @param  mixed  $role  Role key or name.
     *
     * @throws BindingResolutionException
     */
    public static function seesAllProjects(mixed $role): bool
    {
        $roleKey = self::getRoleKey($role);

        if ($roleKey === false) {
            return false;
        }

        $roleName = (string) self::getRoleString($roleKey);

        try {
            return app(PermissionService::class)->roleSeesAllProjects($roleName);
        } catch (\Throwable $e) {
            // No database (install, unit tests). Not logged: this runs on every access check.
            return self::isAtLeast($roleName, self::$admin);
        }
    }

    /**
     * Keys of every role that sees every project, as strings for `whereIn` on `zp_user.role`.
     *
     * @return string[]
     *
     * @throws BindingResolutionException
     */
    public static function keysThatSeeAllProjects(): array
    {
        $matchingKeys = [];

        foreach (array_keys(self::getFilteredRoles()) as $roleKey) {
            if (self::seesAllProjects($roleKey)) {
                $matchingKeys[] = (string) $roleKey;
            }
        }

        return $matchingKeys;
    }

    /**
     * The built-in role a role acts as for checks by role name. A built-in role is its own base.
     * A custom role uses its stored base role, or else the highest built-in role at or below its
     * level. Returns false for an unknown role.
     *
     * @param  mixed  $role  Role key or name.
     *
     * @throws BindingResolutionException
     */
    public static function getBaseRole(mixed $role): string|false
    {
        $roleKey = self::getRoleKey($role);

        if ($roleKey === false) {
            return false;
        }

        if (isset(self::$roleKeys[$roleKey])) {
            return self::$roleKeys[$roleKey];
        }

        $storedBaseRole = self::roleTable()[$roleKey]['baseRole'] ?? null;
        if (is_string($storedBaseRole) && in_array($storedBaseRole, self::$roleKeys, true)) {
            return $storedBaseRole;
        }

        $roleLevel = self::getRoleLevel($roleKey);
        $baseRole = false;
        foreach (self::$roleKeys as $builtInLevel => $builtInName) {
            if ($builtInLevel <= $roleLevel) {
                $baseRole = $builtInName;
            }
        }

        return $baseRole;
    }

    /**
     * Roles that can be given to a user for one project: everything ranked below admin.
     * Admin and owner always apply company-wide.
     *
     * @return array<int, string> [roleKey => roleName]
     *
     * @throws BindingResolutionException
     */
    public static function getProjectAssignableRoles(): array
    {
        return array_filter(
            self::getFilteredRoles(),
            fn ($roleName, $roleKey) => ! self::isAtLeast($roleKey, self::$admin),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Name to show for a role, given its key or name. Built-in roles are translated; custom roles
     * show the display name they were saved with.
     *
     * @param  mixed  $role  Role key or name.
     *
     * @throws BindingResolutionException
     */
    public static function label(mixed $role): string
    {
        $roleKey = self::getRoleKey($role);

        if ($roleKey === false) {
            return '';
        }

        $roleRow = self::roleTable()[$roleKey] ?? null;
        $isCustomRole = $roleRow !== null && ! isset(self::$roleKeys[$roleKey]);

        if ($isCustomRole && ($roleRow['displayName'] ?? '') !== '') {
            return (string) $roleRow['displayName'];
        }

        return __('label.roles.'.self::getRoleString($roleKey));
    }

    /**
     * Drop the cached role table (call after roles change).
     */
    public static function flushCache(): void
    {
        self::forgetLoadedRoles();

        try {
            Cache::store()->forget(self::CACHE_KEY);
        } catch (\Throwable $e) {
            // No cache store yet (early boot / install): nothing to forget.
        }
    }

    /**
     * Forget this process's copy of the role table so the next lookup reads the shared cache
     * again. Long-running workers call this before each job: another process may have changed
     * a role and flushed the shared cache since.
     */
    public static function forgetLoadedRoles(): void
    {
        self::$loadedRoleTables = [];
    }

    /**
     * Role rows by key, lowest level first: [roleKey => [name, displayName, level, baseRole]].
     * Loaded once per tenant per process; built-in roles when the table can't be used.
     */
    private static function roleTable(): array
    {
        $tenantKey = self::tenantKey();

        if (isset(self::$loadedRoleTables[$tenantKey])) {
            return self::$loadedRoleTables[$tenantKey];
        }

        $roleTable = self::loadRoleTable();
        self::$loadedRoleTables[$tenantKey] = $roleTable;

        return $roleTable;
    }

    private static function loadRoleTable(): array
    {
        try {
            $cachedRoleTable = Cache::store()->get(self::CACHE_KEY);
            if (is_array($cachedRoleTable) && $cachedRoleTable !== []) {
                return $cachedRoleTable;
            }

            $roleTable = self::buildRoleTable(app(PermissionRepository::class)->getAllRoles());
        } catch (\Throwable $e) {
            // No database (install, unit tests) or no zp_roles table yet.
            Log::debug('Roles: using built-in roles ('.$e->getMessage().')');

            return self::builtInRoleTable();
        }

        // An unseeded table must not lock everyone out.
        if ($roleTable === []) {
            return self::builtInRoleTable();
        }

        Cache::store()->forever(self::CACHE_KEY, $roleTable);

        return $roleTable;
    }

    /**
     * @param  array<int, array>  $roleRows  Rows from zp_roles.
     */
    private static function buildRoleTable(array $roleRows): array
    {
        $roleTable = [];

        foreach ($roleRows as $roleRow) {
            $roleKey = $roleRow['roleKey'] ?? null;

            // Before the roleKey column existed, built-in rows were keyed by their level.
            if ($roleKey === null && ! empty($roleRow['isSystem'])) {
                $roleKey = $roleRow['level'];
            }

            if ($roleKey === null) {
                continue;
            }

            $roleTable[(int) $roleKey] = [
                'name' => (string) $roleRow['name'],
                'displayName' => (string) ($roleRow['displayName'] ?? ''),
                'level' => (int) $roleRow['level'],
                'baseRole' => $roleRow['baseRole'] ?? null,
            ];
        }

        // A built-in role missing from the table is still a valid role.
        $roleTable += self::builtInRoleTable();

        uasort($roleTable, fn (array $a, array $b) => $a['level'] <=> $b['level']);

        return $roleTable;
    }

    private static function builtInRoleTable(): array
    {
        $roleTable = [];

        foreach (self::$roleKeys as $roleKey => $roleName) {
            $roleTable[$roleKey] = [
                'name' => $roleName,
                'displayName' => '',
                'level' => $roleKey,
                'baseRole' => null,
            ];
        }

        return $roleTable;
    }

    private static function tenantKey(): string
    {
        try {
            return (string) get_domain_key();
        } catch (\Throwable $e) {
            return 'default';
        }
    }
}
