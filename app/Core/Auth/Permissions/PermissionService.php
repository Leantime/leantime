<?php

namespace Leantime\Core\Auth\Permissions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Core\Auth\RoleResolver;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Auth\Models\Roles;

/**
 * The capability engine: the single runtime answer to "may the current user do X?".
 *
 * Consumed everywhere through one method, {@see currentUserCan()}: the JSON-RPC
 * dispatcher and controller bases (via {@see RequiresPermission}), Blade `@can` (via the
 * Gate::before bridge), the menu builder, and in-method `$this->authorize()` helpers.
 *
 * Two concerns are kept strictly separate:
 *  - CAPABILITY — does the user's effective role hold the permission? Resolved against the
 *    cached role->permission grant map. Effective role is project-aware (see {@see RoleResolver}).
 *  - DATA ACCESS — for project-scoped permissions targeting a concrete project, is the user
 *    actually a member of (or otherwise able to access) that project? Admin/owner bypass.
 *
 * Ownership and other entity-specific checks deliberately live in callers, not here.
 */
class PermissionService
{
    private const MAP_CACHE_KEY = 'leantime.permissionMap';

    private const META_CACHE_KEY = 'leantime.permissionMeta';

    /** Set once this request found the role tables unseeded, see {@see map()}. */
    private bool $grantMapIsEmpty = false;

    public function __construct(
        private PermissionRepository $repo,
        private RoleResolver $roles,
        private ChecksProjectAccess $projectAccess,
    ) {}

    /**
     * Whether $roleName holds $permissionKey, by flat lookup on the cached grant map.
     */
    public function roleHasPermission(string $roleName, string $permissionKey): bool
    {
        return in_array($permissionKey, $this->map()[$roleName] ?? [], true);
    }

    /**
     * The authorization decision. Resolves the effective role (project-aware for
     * project-scoped permissions), checks the grant map, then — only for project-scoped
     * permissions against a concrete project — ANDs in project data access.
     *
     * @param  string  $permissionKey  A `domain.action` key.
     * @param  int|null  $projectId  The project the acted-on entity belongs to (for project-scoped checks).
     * @param  bool|null  $forceGlobal  Force the global-role scope (company-wide screens). Null = inferred from the permission.
     */
    public function currentUserCan(string $permissionKey, ?int $projectId = null, ?bool $forceGlobal = null): bool
    {
        $projectScoped = $this->isProjectScoped($permissionKey);
        $useGlobal = $forceGlobal === true || ! $projectScoped;

        if ($useGlobal) {
            $role = $this->roles->effectiveRole(true);
        } elseif ($projectId !== null) {
            $role = $this->roles->effectiveRoleForProject($projectId);
        } else {
            $role = $this->roles->effectiveRole(false);
        }

        if ($role === false || ! $this->roleHasPermission($role, $permissionKey)) {
            return false;
        }

        // Capability granted. Enforce project data access for project-scoped checks.
        if ($projectScoped && $projectId !== null && ! $this->canAccessAllProjects()) {
            return $this->projectAccess->isUserAssignedToProject((int) session('userdata.id'), $projectId);
        }

        return true;
    }

    /**
     * Authorize or throw. Services should call this instead of returning false on denial,
     * so the failure maps cleanly to 403 (web) / RPC -32001.
     *
     * @throws AuthorizationException
     */
    public function authorize(string $permissionKey, ?int $projectId = null, ?bool $forceGlobal = null): void
    {
        if (! $this->currentUserCan($permissionKey, $projectId, $forceGlobal)) {
            // Keep the permission key server-side only (audit/debug); the exception's
            // client-facing message stays generic so we don't expose authz vocabulary.
            Log::info('Authorization denied for permission "'.$permissionKey.'" (user '.(session('userdata.id') ?? 'guest').')');

            throw new AuthorizationException;
        }
    }

    /**
     * Whether $permissionKey is part of the synced vocabulary. Used by the Gate::before
     * bridge to defer (return null) on dotted abilities it does not own.
     */
    public function isManagedPermission(string $permissionKey): bool
    {
        return isset($this->meta()[$permissionKey]);
    }

    /** Whether a permission is evaluated per-project (true) or company-wide (false). */
    public function isProjectScoped(string $permissionKey): bool
    {
        return (bool) ($this->meta()[$permissionKey]['projectScoped'] ?? false);
    }

    /** Forget the cached grant map + vocabulary meta. Call after any role/permission write. */
    public function flushCache(): void
    {
        $this->grantMapIsEmpty = false;
        Cache::store()->forget(self::MAP_CACHE_KEY);
        Cache::store()->forget(self::META_CACHE_KEY);
    }

    /**
     * Admin/owner access every project (mirrors getProjectsUserHasAccessTo's bypass), so
     * they skip the per-project membership check.
     */
    private function canAccessAllProjects(): bool
    {
        $globalRole = $this->roles->globalRole();

        return $globalRole === Roles::$owner || $globalRole === Roles::$admin;
    }

    /**
     * The role -> [permissionKey, ...] grant map, cached on the default (instance-scoped) store;
     * busted via {@see flushCache()}.
     *
     * Grants live in each instance's own database, so the map must never sit on the shared
     * installation store: on a multi-tenant host that one key would serve every tenant whatever
     * grant map was rebuilt last. An empty map is not cached either — it means the role tables
     * are unseeded (every check denies), and caching it would pin that state until the next flush.
     * The empty result is remembered on this instance instead, so a page full of checks reads and
     * logs it once per request. A non-empty map is not held in memory: long-running workers must
     * keep seeing role edits flushed by other processes.
     *
     * @return array<string, array<int, string>>
     */
    private function map(): array
    {
        if ($this->grantMapIsEmpty) {
            return [];
        }

        $cachedMap = Cache::store()->get(self::MAP_CACHE_KEY);

        if (is_array($cachedMap)) {
            return $cachedMap;
        }

        $map = $this->repo->getRolePermissionMap();

        if ($map === []) {
            $this->grantMapIsEmpty = true;
            Log::error('Permission grant map is empty — zp_roles/zp_role_permissions are unseeded, so every permission check denies. Run `php bin/leantime permissions:sync --seed`.');

            return $map;
        }

        Cache::store()->forever(self::MAP_CACHE_KEY, $map);

        return $map;
    }

    /**
     * Vocabulary meta (key => ['projectScoped' => bool]), cached alongside the grant map.
     *
     * @return array<string, array{projectScoped: bool}>
     */
    private function meta(): array
    {
        return Cache::store()->rememberForever(self::META_CACHE_KEY, function () {
            $meta = [];
            foreach ($this->repo->getAllPermissions() as $permission) {
                $meta[$permission['permissionKey']] = ['projectScoped' => (bool) $permission['isProjectScoped']];
            }

            return $meta;
        });
    }
}
