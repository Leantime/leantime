<?php

namespace Leantime\Core\Auth\Permissions;

use Illuminate\Support\Str;

/**
 * The single, central definition of the six built-in roles and the permissions they hold
 * by default — the permission-era equivalent of a Spatie roles-and-permissions seeder.
 *
 * This is the ONLY place built-in role→permission assignment lives. Domains declare verbs
 * ({@see ProvidesPermissions}); this maps those verbs onto roles. After install the
 * `zp_role_permissions` table is the runtime source of truth and the admin UI edits it —
 * this class only provides the initial defaults.
 *
 * Defaults are expressed as incremental grant rules per role and **unioned up the
 * hierarchy** (a role inherits every lower role's grants), so the rules read as deltas:
 *  - readonly  : view project content
 *  - commenter : + comment / upload
 *  - editor    : + create / edit / delete
 *  - manager   : + everything else on project content (e.g. project settings)
 *  - admin     : + all company-wide capabilities, except company settings
 *  - owner     : + everything (incl. company settings)
 *
 * A rule matches a {@see Permission} by scope (project- vs company-scoped), by verb (the
 * last dotted segment, or `*` for all), minus any excluded keys/prefixes.
 */
final class DefaultRolePermissions
{
    /**
     * Built-in roles, ordered low→high. `level` preserves the legacy hierarchy weight.
     *
     * @var array<int, array{name: string, displayName: string, level: int}>
     */
    private const ROLES = [
        ['name' => 'readonly', 'displayName' => 'Read Only', 'level' => 5],
        ['name' => 'commenter', 'displayName' => 'Commenter', 'level' => 10],
        ['name' => 'editor', 'displayName' => 'Editor', 'level' => 20],
        ['name' => 'manager', 'displayName' => 'Company Manager', 'level' => 30],
        ['name' => 'admin', 'displayName' => 'Admin', 'level' => 40],
        ['name' => 'owner', 'displayName' => 'Owner', 'level' => 50],
    ];

    /**
     * Incremental default grants per role (unioned up the hierarchy by {@see grantsFor()}).
     *
     * Each rule: scope = project|global|any; verbs = list of last-segment verbs or ['*'];
     * exclude = exact keys or 'prefix.*' globs removed from the match.
     *
     * @var array<string, array<int, array{scope: string, verbs: array<int, string>, exclude?: array<int, string>}>>
     */
    private const GRANTS = [
        'readonly' => [['scope' => 'project', 'verbs' => ['view']]],
        'commenter' => [['scope' => 'project', 'verbs' => ['comment', 'upload']]],
        'editor' => [['scope' => 'project', 'verbs' => ['create', 'edit', 'delete']]],
        'manager' => [['scope' => 'project', 'verbs' => ['*']]],
        'admin' => [['scope' => 'any', 'verbs' => ['*'], 'exclude' => ['company.settings.*']]],
        'owner' => [['scope' => 'any', 'verbs' => ['*']]],
    ];

    /** @return array<int, array{name: string, displayName: string, level: int}> */
    public static function roles(): array
    {
        return self::ROLES;
    }

    /**
     * The default permission keys granted to $roleName, given the full discovered catalog.
     * Unions this role's rules with every lower role in the hierarchy.
     *
     * @param  array<int, Permission>  $catalog
     * @return array<int, string>
     */
    public static function grantsFor(string $roleName, array $catalog): array
    {
        $level = self::levelOf($roleName);

        $rules = [];
        foreach (self::ROLES as $role) {
            if ($role['level'] <= $level) {
                foreach (self::GRANTS[$role['name']] ?? [] as $rule) {
                    $rules[] = $rule;
                }
            }
        }

        $keys = [];
        foreach ($catalog as $permission) {
            foreach ($rules as $rule) {
                if (self::matches($permission, $rule)) {
                    $keys[$permission->key] = true;
                    break;
                }
            }
        }

        return array_keys($keys);
    }

    private static function levelOf(string $roleName): int
    {
        foreach (self::ROLES as $role) {
            if ($role['name'] === $roleName) {
                return $role['level'];
            }
        }

        return 0;
    }

    /**
     * @param  array{scope: string, verbs: array<int, string>, exclude?: array<int, string>}  $rule
     */
    private static function matches(Permission $permission, array $rule): bool
    {
        if ($rule['scope'] === 'project' && ! $permission->projectScoped) {
            return false;
        }
        if ($rule['scope'] === 'global' && $permission->projectScoped) {
            return false;
        }

        $verb = Str::afterLast($permission->key, '.');
        if ($rule['verbs'] !== ['*'] && ! in_array($verb, $rule['verbs'], true)) {
            return false;
        }

        foreach ($rule['exclude'] ?? [] as $excluded) {
            if ($excluded === $permission->key) {
                return false;
            }
            if (str_ends_with($excluded, '.*') && str_starts_with($permission->key, substr($excluded, 0, -1))) {
                return false;
            }
        }

        return true;
    }
}
