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
     * A rule grants by `verbs` (the convention) OR by explicit `keys` (for permissions that
     * don't follow the verb convention).
     *
     * @var array<string, array<int, array{scope: string, verbs?: array<int, string>, keys?: array<int, string>, exclude?: array<int, string>}>>
     */
    private const GRANTS = [
        'readonly' => [['scope' => 'project', 'verbs' => ['view']]],
        'commenter' => [
            ['scope' => 'project', 'verbs' => ['comment', 'upload']],
            // Commenting via the Comments domain: the 'create' verb otherwise seeds at
            // editor+, but a commenter is allowed to add comments — grant the key explicitly.
            ['scope' => 'project', 'keys' => ['comments.create']],
        ],
        'editor' => [
            ['scope' => 'project', 'verbs' => ['create', 'edit', 'delete']],
            // Timesheets are GLOBAL-scoped (company-wide time logging), so the project verb rule
            // above does NOT match them — an editor's own-time capability is granted by explicit
            // global keys. Ownership (own vs others) is enforced in the service; the cross-user
            // `timesheets.manage` stays manager+ (below).
            ['scope' => 'global', 'keys' => ['timesheets.view', 'timesheets.create', 'timesheets.edit', 'timesheets.delete']],
        ],
        'manager' => [
            ['scope' => 'project', 'verbs' => ['*']],
            // Managers may INVITE users (the NewUser screen is manager+). The client-scoping
            // — a manager can only invite into their own client — stays in the controller/
            // service, not here. They CANNOT view the roster, edit, delete, or import users;
            // those remain admin+. users.* are company-wide, so this is an explicit global
            // key grant rather than a 'create' verb rule (a verb rule would also need a global
            // scope and is fine, but the explicit key documents that ONLY create is intended).
            // timesheets.manage (company-wide invoicing/reports/others' time) is manager+; the
            // editor keys above are inherited up the hierarchy.
            ['scope' => 'global', 'keys' => ['users.create', 'timesheets.manage']],
        ],
        'admin' => [
            ['scope' => 'any', 'verbs' => ['*'], 'exclude' => ['company.settings.*']],
            // Admins may view AND edit the company-settings screen (incl. logo) per policy. The
            // exclude above keeps any OTHER future company.settings.* owner-only by default;
            // these two keys are granted to admins explicitly.
            ['scope' => 'global', 'keys' => ['company.settings.view', 'company.settings.edit']],
        ],
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
     * @param  array{scope: string, verbs?: array<int, string>, keys?: array<int, string>, exclude?: array<int, string>}  $rule
     */
    private static function matches(Permission $permission, array $rule): bool
    {
        if ($rule['scope'] === 'project' && ! $permission->projectScoped) {
            return false;
        }
        if ($rule['scope'] === 'global' && $permission->projectScoped) {
            return false;
        }

        // A rule matches by EITHER an explicit key allow-list (for permissions that don't follow
        // the verb convention) OR by verb. Compute the base match first...
        if (isset($rule['keys'])) {
            $matched = in_array($permission->key, $rule['keys'], true);
        } else {
            $verb = Str::afterLast($permission->key, '.');
            $matched = ($rule['verbs'] ?? []) === ['*'] || in_array($verb, $rule['verbs'] ?? [], true);
        }

        if (! $matched) {
            return false;
        }

        // ...then ALWAYS apply the exclude list, so an `exclude` alongside `keys` is honored (a
        // `keys` rule previously returned early and bypassed the exclude, risking an over-grant).
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
