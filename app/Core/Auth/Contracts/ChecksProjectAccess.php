<?php

namespace Leantime\Core\Auth\Contracts;

/**
 * Narrow contract the permission engine depends on for project-level data access and the
 * per-project role, implemented by the Projects domain service.
 *
 * The engine lives in Core and must not depend on the 2,900-line Projects god-service
 * directly; it depends on this abstraction (bound to Projects in PermissionServiceProvider),
 * which also keeps the surface small and sidesteps circular-reference risk.
 */
interface ChecksProjectAccess
{
    /**
     * Whether the user can access the given project (assigned, or via the project's
     * `psettings` — admin/owner bypass handled by the implementation/engine).
     */
    public function isUserAssignedToProject(int $userId, int $projectId): bool;

    /**
     * The user's explicit role within the project, or '' when none is set (inherits global).
     * Returns the stored role key as a string.
     */
    public function getProjectRole(int $userId, int $projectId): string;
}
