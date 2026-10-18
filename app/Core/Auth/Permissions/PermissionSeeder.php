<?php

namespace Leantime\Core\Auth\Permissions;

/**
 * Seeds the database-backed permission system from code declarations.
 *
 * Two idempotent operations:
 *  - {@see syncDiscoveredPermissions()} writes the discovered `domain.action` vocabulary
 *    into zp_permissions. Safe to run anytime. The only grants it writes are for permissions
 *    that are NEW in this sync: each custom role gets those its base role gets by default, so
 *    features added by an update work for custom roles. Existing grants are never touched, so
 *    administrator customizations survive a re-sync.
 *  - {@see seedBuiltInRoles()} upserts the six built-in roles and ADDITIVELY grants each its
 *    default permissions, resolved from the central {@see DefaultRolePermissions} matrix
 *    against the discovered catalog. Additive grants never remove an administrator's edits.
 *
 * Vocabulary must be synced before grants can reference it, so the install migration calls
 * sync first, then seed.
 */
class PermissionSeeder
{
    public function __construct(
        private PermissionRepository $repo,
        private PermissionRegistry $registry,
        private PermissionService $permissions,
    ) {}

    /**
     * Upsert the discovered vocabulary into zp_permissions and bust the engine cache.
     *
     * @return array<int, string> The synced permission keys.
     */
    public function syncDiscoveredPermissions(): array
    {
        $definitions = array_map(
            fn (Permission $p): array => [
                'key' => $p->key,
                'domain' => $p->domain(),
                'action' => $p->action(),
                'label' => $p->displayName,
                'projectScoped' => $p->projectScoped,
            ],
            array_values($this->registry->all()),
        );

        $keysBeforeSync = array_column($this->repo->getAllPermissions(), 'permissionKey');

        $keys = $this->repo->syncPermissions($definitions);

        $this->grantNewPermissionsToCustomRoles(array_diff($keys, $keysBeforeSync));
        $this->permissions->flushCache();

        return $keys;
    }

    /**
     * Give each custom role the new permissions its base role gets by default
     * ({@see DefaultRolePermissions}). Built-in roles get theirs from {@see seedBuiltInRoles()}.
     *
     * @param  array<int, string>  $newPermissionKeys  Keys that weren't in zp_permissions before this sync.
     */
    private function grantNewPermissionsToCustomRoles(array $newPermissionKeys): void
    {
        if ($newPermissionKeys === []) {
            return;
        }

        $newPermissions = array_values(array_intersect_key($this->registry->all(), array_flip($newPermissionKeys)));

        foreach ($this->repo->getAllRoles() as $roleRow) {
            if (! empty($roleRow['isSystem'])) {
                continue;
            }

            $baseRole = $this->baseRoleOf($roleRow);
            if ($baseRole === null) {
                continue;
            }

            foreach (DefaultRolePermissions::grantsFor($baseRole, $newPermissions) as $permissionKey) {
                $this->repo->grant((int) $roleRow['id'], $permissionKey);
            }
        }
    }

    /**
     * The built-in role a custom role is based on: its stored base role, or else the highest
     * built-in role at or below its level. Null when it ranks below every built-in role.
     *
     * @param  array<string, mixed>  $roleRow  A zp_roles row.
     */
    private function baseRoleOf(array $roleRow): ?string
    {
        $builtInRoleLevels = array_column(DefaultRolePermissions::roles(), 'level', 'name');

        $storedBaseRole = $roleRow['baseRole'] ?? null;
        if (is_string($storedBaseRole) && isset($builtInRoleLevels[$storedBaseRole])) {
            return $storedBaseRole;
        }

        $baseRole = null;
        foreach ($builtInRoleLevels as $builtInName => $builtInLevel) {
            if ($builtInLevel <= (int) $roleRow['level']) {
                $baseRole = $builtInName;
            }
        }

        return $baseRole;
    }

    /**
     * Upsert the six built-in roles and additively grant their default permissions from the
     * central {@see DefaultRolePermissions} matrix.
     */
    public function seedBuiltInRoles(): void
    {
        $catalog = array_values($this->registry->all());
        $roleIds = [];

        foreach (DefaultRolePermissions::roles() as $role) {
            $roleIds[$role['name']] = $this->repo->upsertRole(
                $role['name'],
                $role['displayName'],
                $role['level'],
                isSystem: true,
                // Built-in roles are stored in zp_user.role by their level (5..50).
                roleKey: $role['level'],
            );
        }

        foreach (DefaultRolePermissions::roles() as $role) {
            foreach (DefaultRolePermissions::grantsFor($role['name'], $catalog) as $permissionKey) {
                $this->repo->grant($roleIds[$role['name']], $permissionKey);
            }
        }

        $this->permissions->flushCache();
    }
}
