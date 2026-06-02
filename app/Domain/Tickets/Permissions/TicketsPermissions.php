<?php

namespace Leantime\Domain\Tickets\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Tickets (to-do) permission vocabulary — the verbs only.
 *
 * Declares *what* can be done with tickets; it says nothing about *which roles* may do it.
 * Role assignment is centrally owned (defaults in {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions},
 * runtime assignments in `zp_role_permissions` / the admin UI). The typed constants are
 * used at call sites (controllers, `@api` methods, `@can`, menu) so nobody types a raw string.
 *
 * All ticket capabilities are project-scoped — a ticket belongs to a project, so authority
 * is the user's role *in that project* (see {@see \Leantime\Core\Auth\RoleResolver}).
 */
final class TicketsPermissions implements ProvidesPermissions
{
    public const VIEW = 'tickets.view';

    public const COMMENT = 'tickets.comment';

    public const UPLOAD = 'tickets.upload';

    public const CREATE = 'tickets.create';

    public const EDIT = 'tickets.edit';

    public const DELETE = 'tickets.delete';

    public function domain(): string
    {
        return 'tickets';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View to-dos'),
            new Permission(self::COMMENT, 'Comment on to-dos'),
            new Permission(self::UPLOAD, 'Upload files to to-dos'),
            new Permission(self::CREATE, 'Create to-dos'),
            new Permission(self::EDIT, 'Edit to-dos'),
            new Permission(self::DELETE, 'Delete to-dos'),
        ];
    }
}
