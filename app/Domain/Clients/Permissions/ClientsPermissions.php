<?php

namespace Leantime\Domain\Clients\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Clients (company account management) permission vocabulary — the verbs only.
 *
 * Declares *what* can be done with clients; it says nothing about *which roles* may do it
 * (role assignment is centrally owned — see {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions}).
 *
 * Every client capability is COMPANY-WIDE, not project-scoped: clients are a company resource,
 * so authority is the user's GLOBAL role (see {@see \Leantime\Core\Auth\RoleResolver}). Each
 * Permission below is constructed with `projectScoped = false`, and call sites gate with
 * `#[RequiresPermission(..., global: true)]`.
 *
 * admin + owner hold all `clients.*` via the company-wide wildcard in DefaultRolePermissions;
 * lower roles hold none — matching today's behavior, where all client management is admin+.
 */
final class ClientsPermissions implements ProvidesPermissions
{
    /** View the client roster / read a client. */
    public const VIEW = 'clients.view';

    /** Create clients. */
    public const CREATE = 'clients.create';

    /** Edit a client (and manage its user assignments). */
    public const EDIT = 'clients.edit';

    /** Delete clients (cascades to their projects). */
    public const DELETE = 'clients.delete';

    public function domain(): string
    {
        return 'clients';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View clients', false),
            new Permission(self::CREATE, 'Create clients', false),
            new Permission(self::EDIT, 'Edit clients', false),
            new Permission(self::DELETE, 'Delete clients', false),
        ];
    }
}
