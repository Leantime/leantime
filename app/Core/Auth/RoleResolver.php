<?php

namespace Leantime\Core\Auth;

use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;

/**
 * Single home for resolving a user's EFFECTIVE role, in both scopes Leantime cares about.
 *
 * Leantime roles are project-scoped: a user can hold one role globally
 * (`session('userdata.role')`) and a different role inside a given project
 * (`zp_relationuserproject.projectRole`). Authorization correctness depends on picking
 * the right one:
 *  - {@see effectiveRole()} resolves the role for the CURRENT SESSION project — the
 *    historical behavior of `Auth::getRoleToCheck()`, which this delegates to (no
 *    duplication). Use it for "is the current screen allowed" checks.
 *  - {@see effectiveRoleForProject()} resolves the role for a SPECIFIC project — the
 *    only correct basis for authorizing a mutation on an entity that may live outside
 *    the session project. It centralizes the logic previously private to
 *    `Tickets::userIsAtLeastForProject()`.
 *
 * This is infrastructure shared across every domain, hence it lives in Core/Auth. It
 * still references the Domain-layer `Roles` definitions and `Projects` service for now;
 * those are stable value/lookup surfaces and the coupling is intentional pragmatism
 * (the broader Roles->Core move is deferred).
 */
class RoleResolver
{
    public function __construct(private ChecksProjectAccess $projectAccess) {}

    /** The current user's global role string, or null when not authenticated. */
    public function globalRole(): ?string
    {
        $role = session('userdata.role');

        return ($role === null || $role === '') ? null : $role;
    }

    /**
     * Effective role for the current session project (delegates to the existing
     * dual-scope resolution). `$forceGlobal` short-circuits to the global role for
     * company-wide screens (users/clients/settings).
     */
    public function effectiveRole(bool $forceGlobal = false): string|false
    {
        return AuthService::getRoleToCheck($forceGlobal);
    }

    /**
     * Effective role for a specific project. Manager/admin/owner keep their global role
     * everywhere; otherwise the explicit project role applies, falling back to the
     * global role when none is set. Returns false when not authenticated.
     */
    public function effectiveRoleForProject(int $projectId): string|false
    {
        $globalRole = $this->globalRole();

        if ($globalRole === null) {
            return false;
        }

        $roles = Roles::getRoles();
        $globalKey = array_search($globalRole, $roles, true);
        $managerKey = array_search(Roles::$manager, $roles, true);

        // Manager and above keep their global role across every project.
        if ($globalKey !== false && $managerKey !== false && $globalKey >= $managerKey) {
            return $globalRole;
        }

        $projectRole = $this->projectAccess->getProjectRole((int) session('userdata.id'), $projectId);

        // No explicit project role -> inherit the global role.
        return $projectRole === '' ? $globalRole : Roles::getRoleString((int) $projectRole);
    }

    /**
     * True when $effectiveRole ranks at or above $requiredRole in the role hierarchy,
     * using the same ordering as {@see Roles::getRoles()}.
     */
    public function atLeast(string $requiredRole, string|false $effectiveRole): bool
    {
        if ($effectiveRole === false) {
            return false;
        }

        $roles = Roles::getRoles();
        $requiredKey = array_search($requiredRole, $roles, true);
        $effectiveKey = array_search($effectiveRole, $roles, true);

        return $requiredKey !== false && $effectiveKey !== false && $effectiveKey >= $requiredKey;
    }
}
