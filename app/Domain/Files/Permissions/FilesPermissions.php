<?php

namespace Leantime\Domain\Files\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Files permission vocabulary — standard project-scoped capabilities.
 *
 * A file's authority is the user's role *in the file's owning project*: project-module files
 * use their moduleId as the projectId directly, ticket-module files resolve through the owning
 * ticket. Both are resolved fail-closed by {@see \Leantime\Domain\Files\Repositories\Files::getProjectIdForFile()}
 * (null when the id is missing or the module has no project context).
 *
 * All three verbs are standard, so they auto-grant through the project-scoped matrix rules in
 * {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions} with NO matrix edit:
 *  - view   → readonly+ (any project member may see/download a project's files)
 *  - upload → commenter+ (the standard `upload` verb, same as ticket/comment attachments)
 *  - delete → editor+ (manager+ via the project wildcard; admin/owner via scope:any)
 *
 * Two checks live in the service rather than the vocabulary:
 *  - Ownership: a file's uploader may always delete their own file regardless of role.
 *  - Owner-restricted modules (private/user/lead/export) have no project context — access is
 *    gated by the uploader check instead of a project permission.
 */
final class FilesPermissions implements ProvidesPermissions
{
    /** View / download files attached to a project or project entity (ticket). Readonly+. */
    public const VIEW = 'files.view';

    /** Upload files to a project or project entity. Commenter+. */
    public const UPLOAD = 'files.upload';

    /** Delete another user's file in a project (the uploader may always delete their own). Editor+. */
    public const DELETE = 'files.delete';

    public function domain(): string
    {
        return 'files';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View and download project files', true),
            new Permission(self::UPLOAD, 'Upload files to projects', true),
            new Permission(self::DELETE, 'Delete project files', true),
        ];
    }
}
