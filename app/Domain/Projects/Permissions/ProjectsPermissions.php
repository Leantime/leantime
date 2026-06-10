<?php

namespace Leantime\Domain\Projects\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Projects permission vocabulary.
 *
 * Two scopes, because Projects mixes a company-level management capability with per-project data
 * reads:
 *
 *  - VIEW is PROJECT-scoped (readonly+): reading a project's data (name, progress, settings,
 *    avatar, integration config) by id. It auto-grants through the matrix and the project-scoped
 *    check AND-ins data access (isUserAssignedToProject), so a caller can only read projects they
 *    already have access to. This closes the cross-project read IDOR on the @api reads without
 *    changing who can see their own projects.
 *
 *  - CREATE / EDIT / DELETE are GLOBAL-scoped, MANAGER+ (admin/owner via scope:any; an explicit
 *    manager rule in {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions} grants them —
 *    editors do NOT get them). This is grant-equivalent to the legacy controllers, which gate
 *    project management on the GLOBAL manager role (authOrRedirect([owner,admin,manager],
 *    forceGlobalRoleCheck: true)). A manager manages any project company-wide; being global-scoped
 *    they sidestep the project-membership AND-in (and the ChecksProjectAccess recursion path).
 *    Project-membership management (assign users, roles) folds into EDIT.
 *
 * NOT represented here (deliberately): the access-resolution methods getProjectRole() and
 * isUserAssignedToProject() — the permission engine itself calls those during every project-scoped
 * authorization, so they must remain ungated (an in-body authorize would recurse infinitely).
 */
final class ProjectsPermissions implements ProvidesPermissions
{
    /** Read a project's data by id (project-scoped, readonly+; AND-ins data access). */
    public const VIEW = 'projects.view';

    /** Create a project. Manager+ (global, company-wide). */
    public const CREATE = 'projects.create';

    /** Edit a project's settings / membership / integrations. Manager+ (global, company-wide). */
    public const EDIT = 'projects.edit';

    /** Delete a project. Manager+ (global, company-wide). */
    public const DELETE = 'projects.delete';

    public function domain(): string
    {
        return 'projects';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View a project', true),
            new Permission(self::CREATE, 'Create projects', false),
            new Permission(self::EDIT, 'Edit projects', false),
            new Permission(self::DELETE, 'Delete projects', false),
        ];
    }
}
