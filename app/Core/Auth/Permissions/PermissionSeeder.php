<?php

namespace Leantime\Core\Auth\Permissions;

/**
 * Seeds the database-backed permission system from code declarations.
 *
 * Two idempotent operations:
 *  - {@see syncDiscoveredPermissions()} writes the discovered `domain.action` vocabulary
 *    into zp_permissions. Safe to run anytime; never touches role grants, so administrator
 *    customizations survive a re-sync.
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

        $keys = $this->repo->syncPermissions($definitions);
        $this->permissions->flushCache();

        return $keys;
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
