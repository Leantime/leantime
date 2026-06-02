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
        Cache::store('installation')->forget(self::MAP_CACHE_KEY);
        Cache::store('installation')->forget(self::META_CACHE_KEY);
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
     * The role -> [permissionKey, ...] grant map, cached on the shared installation store
     * (file/Redis) so all workers share it; busted via {@see flushCache()}.
     *
     * @return array<string, array<int, string>>
     */
    private function map(): array
    {
        return Cache::store('installation')->rememberForever(self::MAP_CACHE_KEY, fn () => $this->repo->getRolePermissionMap());
    }

    /**
     * Vocabulary meta (key => ['projectScoped' => bool]), cached alongside the grant map.
     *
     * @return array<string, array{projectScoped: bool}>
     */
    private function meta(): array
    {
        return Cache::store('installation')->rememberForever(self::META_CACHE_KEY, function () {
            $meta = [];
            foreach ($this->repo->getAllPermissions() as $permission) {
                $meta[$permission['permissionKey']] = ['projectScoped' => (bool) $permission['isProjectScoped']];
            }

            return $meta;
        });
    }
}
