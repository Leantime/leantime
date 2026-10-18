<?php

namespace Leantime\Core\Auth\Permissions;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

/**
 * Data access for the native permission engine (no ORM — Laravel query builder over the
 * `zp_roles`, `zp_permissions`, `zp_role_permissions` tables).
 *
 * Roles are the DB-backed definitions (built-ins + custom); permissions are the synced
 * `domain.action` vocabulary; the grant map links them. This repository is consumed by
 * {@see PermissionService} (read path, cached), {@see PermissionSeeder} (built-in seeding +
 * vocabulary sync), `permissions:sync`, and the future role-management UI (write path).
 */
class PermissionRepository
{
    private ConnectionInterface $db;

    /** Custom role keys are this plus the role's id, so they never overlap the built-in keys (5..50). */
    public const CUSTOM_ROLE_KEY_OFFSET = 100;

    private ?bool $roleKeyColumnsExist = null;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * Run several writes as one unit on the permission tables' connection.
     *
     * @template T
     *
     * @param  \Closure(): T  $writes
     * @return T
     */
    public function transaction(\Closure $writes): mixed
    {
        return $this->db->transaction($writes);
    }

    // ------------------------------------------------------------------
    // Roles
    // ------------------------------------------------------------------

    /** @return array<string, mixed>|null */
    public function getRoleByName(string $name): ?array
    {
        $row = $this->db->table('zp_roles')->where('name', $name)->first();

        return $row ? (array) $row : null;
    }

    /** @return array<string, mixed>|null */
    public function getRoleById(int $id): ?array
    {
        $row = $this->db->table('zp_roles')->where('id', $id)->first();

        return $row ? (array) $row : null;
    }

    /**
     * All roles ordered by hierarchy level (ascending).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllRoles(): array
    {
        return $this->db->table('zp_roles')
            ->orderBy('level')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /**
     * Insert or update a role keyed by its (unique) name. Returns the role id.
     * Built-in roles pass isSystem=true so the admin UI can protect them.
     *
     * @param  int|null  $roleKey  Value stored in zp_user.role for this role; null leaves it unchanged.
     * @param  string|null  $baseRole  Built-in role a custom role acts as for checks by role name.
     */
    public function upsertRole(
        string $name,
        string $displayName,
        int $level,
        bool $isSystem = false,
        ?string $description = null,
        ?int $roleKey = null,
        ?string $baseRole = null,
    ): int {
        $now = dtHelper()->userNow()->formatDateTimeForDb();

        $row = [
            'displayName' => $displayName,
            'level' => $level,
            'isSystem' => $isSystem ? 1 : 0,
            'description' => $description,
            'modified' => $now,
        ];

        // Older migrations seed roles before update_sql_30532 adds these columns.
        if ($this->hasRoleKeyColumns()) {
            if ($roleKey !== null) {
                $row['roleKey'] = $roleKey;
            }
            $row['baseRole'] = $baseRole;
        }

        $existing = $this->getRoleByName($name);

        if ($existing !== null) {
            $this->db->table('zp_roles')->where('id', $existing['id'])->update($row);

            return (int) $existing['id'];
        }

        return (int) $this->db->table('zp_roles')->insertGetId($row + [
            'name' => $name,
            'createdOn' => $now,
        ]);
    }

    /**
     * Create (or update by name) a role from an array. A new role without a `roleKey` gets a
     * custom key derived from its row id (100 + id): ids are never reused, so neither are keys,
     * and two roles created at the same time can't get the same key.
     *
     * @param  array<string, mixed>  $data
     */
    public function createRole(array $data): int
    {
        $existing = $this->getRoleByName((string) $data['name']);

        $roleId = $this->upsertRole(
            (string) $data['name'],
            (string) ($data['displayName'] ?? $data['name']),
            (int) ($data['level'] ?? 20),
            (bool) ($data['isSystem'] ?? false),
            $data['description'] ?? null,
            isset($data['roleKey']) ? (int) $data['roleKey'] : null,
            $data['baseRole'] ?? null,
        );

        $needsCustomKey = ! isset($data['roleKey']) && ($existing['roleKey'] ?? null) === null;

        if ($needsCustomKey && $this->hasRoleKeyColumns()) {
            $this->db->table('zp_roles')
                ->where('id', $roleId)
                ->update(['roleKey' => self::CUSTOM_ROLE_KEY_OFFSET + $roleId]);
        }

        return $roleId;
    }

    private function hasRoleKeyColumns(): bool
    {
        if ($this->roleKeyColumnsExist === null) {
            $this->roleKeyColumnsExist = $this->db instanceof Connection
                && $this->db->getSchemaBuilder()->hasColumn('zp_roles', 'roleKey');
        }

        return $this->roleKeyColumnsExist;
    }

    /** @param  array<string, mixed>  $data */
    public function updateRole(int $id, array $data): bool
    {
        $data['modified'] = dtHelper()->userNow()->formatDateTimeForDb();

        return (bool) $this->db->table('zp_roles')->where('id', $id)->update($data);
    }

    /**
     * Deletes a role and its grants. Refuses (returns false) for built-in roles and for roles
     * that a user, API key or project membership still holds: those rows store the role's key,
     * and must be moved to another role first.
     */
    public function deleteRole(int $id): bool
    {
        $role = $this->getRoleById($id);

        if ($role === null || ! empty($role['isSystem'])) {
            return false;
        }

        if ($this->isRoleAssigned($role)) {
            return false;
        }

        $this->db->table('zp_role_permissions')->where('roleId', $id)->delete();

        return (bool) $this->db->table('zp_roles')->where('id', $id)->delete();
    }

    /**
     * Whether any user (incl. API keys) or project membership stores this role's key.
     *
     * @param  array<string, mixed>  $role  A zp_roles row.
     */
    public function isRoleAssigned(array $role): bool
    {
        $roleKey = $role['roleKey'] ?? null;

        if ($roleKey === null) {
            return false;
        }

        $storedRoleKey = (string) $roleKey;

        $heldByUser = $this->db->table('zp_user')->where('role', $storedRoleKey)->exists();
        if ($heldByUser) {
            return true;
        }

        return $this->db->table('zp_relationuserproject')->where('projectRole', $storedRoleKey)->exists();
    }

    // ------------------------------------------------------------------
    // Permissions (the vocabulary)
    // ------------------------------------------------------------------

    /** @return array<int, array<string, mixed>> */
    public function getAllPermissions(): array
    {
        return $this->db->table('zp_permissions')
            ->orderBy('permissionKey')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /** @return array<string, mixed>|null */
    public function getPermissionByKey(string $key): ?array
    {
        $row = $this->db->table('zp_permissions')->where('permissionKey', $key)->first();

        return $row ? (array) $row : null;
    }

    /**
     * Idempotently upsert the discovered vocabulary into zp_permissions, keyed by
     * permissionKey. Returns the full list of synced keys (for optional pruning).
     *
     * @param  array<int, array{key:string, domain:string, action:string, label:string, projectScoped:bool}>  $definitions
     * @return array<int, string> The synced permission keys.
     */
    public function syncPermissions(array $definitions): array
    {
        $now = dtHelper()->userNow()->formatDateTimeForDb();
        $keys = [];

        foreach ($definitions as $def) {
            $keys[] = $def['key'];
            $row = [
                'domain' => $def['domain'],
                'action' => $def['action'],
                'label' => $def['label'],
                'isProjectScoped' => $def['projectScoped'] ? 1 : 0,
                'modified' => $now,
            ];

            if ($this->getPermissionByKey($def['key']) !== null) {
                $this->db->table('zp_permissions')->where('permissionKey', $def['key'])->update($row);

                continue;
            }

            $this->db->table('zp_permissions')->insert($row + [
                'permissionKey' => $def['key'],
                'createdOn' => $now,
            ]);
        }

        return $keys;
    }

    /**
     * Remove permissions (and their grants) whose key is not in $keepKeys. Returns the
     * number of pruned permissions. Used by `permissions:sync --prune`.
     *
     * @param  array<int, string>  $keepKeys
     */
    public function pruneOrphanPermissions(array $keepKeys): int
    {
        $orphans = $this->db->table('zp_permissions')
            ->when($keepKeys !== [], fn ($q) => $q->whereNotIn('permissionKey', $keepKeys))
            ->pluck('id')
            ->all();

        if ($orphans === []) {
            return 0;
        }

        $this->db->table('zp_role_permissions')->whereIn('permissionId', $orphans)->delete();

        return $this->db->table('zp_permissions')->whereIn('id', $orphans)->delete();
    }

    // ------------------------------------------------------------------
    // Grants (role <-> permission map)
    // ------------------------------------------------------------------

    /**
     * The full role -> [permissionKey, ...] map driving runtime checks. One JOIN; the
     * caller ({@see PermissionService}) caches the result.
     *
     * @return array<string, array<int, string>>
     */
    public function getRolePermissionMap(): array
    {
        $rows = $this->db->table('zp_role_permissions as rp')
            ->join('zp_roles as r', 'r.id', '=', 'rp.roleId')
            ->join('zp_permissions as p', 'p.id', '=', 'rp.permissionId')
            ->select('r.name as roleName', 'p.permissionKey as permissionKey')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->roleName][] = $row->permissionKey;
        }

        return $map;
    }

    /**
     * Replace a role's grants with exactly the given permission keys (transactional).
     *
     * @param  array<int, string>  $permissionKeys
     */
    public function replaceRolePermissions(int $roleId, array $permissionKeys): void
    {
        $this->db->transaction(function () use ($roleId, $permissionKeys) {
            $this->db->table('zp_role_permissions')->where('roleId', $roleId)->delete();

            if ($permissionKeys === []) {
                return;
            }

            $ids = $this->db->table('zp_permissions')
                ->whereIn('permissionKey', $permissionKeys)
                ->pluck('id')
                ->all();

            $rows = array_map(fn ($id) => ['roleId' => $roleId, 'permissionId' => $id], $ids);

            if ($rows !== []) {
                $this->db->table('zp_role_permissions')->insert($rows);
            }
        });
    }

    /** Grant a single permission to a role (no-op if already granted). */
    public function grant(int $roleId, string $permissionKey): void
    {
        $permission = $this->getPermissionByKey($permissionKey);
        if ($permission === null) {
            return;
        }

        $exists = $this->db->table('zp_role_permissions')
            ->where('roleId', $roleId)
            ->where('permissionId', $permission['id'])
            ->exists();

        if (! $exists) {
            $this->db->table('zp_role_permissions')->insert([
                'roleId' => $roleId,
                'permissionId' => $permission['id'],
            ]);
        }
    }

    /** Revoke a single permission from a role. */
    public function revoke(int $roleId, string $permissionKey): void
    {
        $permission = $this->getPermissionByKey($permissionKey);
        if ($permission === null) {
            return;
        }

        $this->db->table('zp_role_permissions')
            ->where('roleId', $roleId)
            ->where('permissionId', $permission['id'])
            ->delete();
    }
}
