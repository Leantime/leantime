<?php

namespace Leantime\Domain\Users\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Users (account management) permission vocabulary — the verbs only.
 *
 * Declares *what* can be done with user accounts; it says nothing about *which roles* may do
 * it. Role assignment is centrally owned (defaults in
 * {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions}, runtime in `zp_role_permissions`).
 *
 * Every user capability is COMPANY-WIDE, not project-scoped: managing accounts is authority a
 * user holds across the whole installation, evaluated against their GLOBAL role (see
 * {@see \Leantime\Core\Auth\RoleResolver}). So every Permission below is constructed with
 * `projectScoped = false`, and call sites gate with `#[RequiresPermission(..., global: true)]`.
 *
 * These verbs cover MANAGING OTHER accounts only. Editing one's OWN profile/settings/avatar is
 * self-service that every authenticated user may do and is deliberately NOT gated by any
 * `users.*` permission (see {@see \Leantime\Domain\Users\Controllers\EditOwn} and the
 * self-service methods on the Users service).
 */
final class UsersPermissions implements ProvidesPermissions
{
    /** View the company user roster / read another account. */
    public const VIEW = 'users.view';

    /** Invite / create new accounts. */
    public const CREATE = 'users.create';

    /** Edit another account (role, client, status, profile fields). */
    public const EDIT = 'users.edit';

    /** Delete accounts. */
    public const DELETE = 'users.delete';

    /** Bulk-import accounts from a directory (LDAP). */
    public const IMPORT = 'users.import';

    public function domain(): string
    {
        return 'users';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View users', false),
            new Permission(self::CREATE, 'Invite/create users', false),
            new Permission(self::EDIT, 'Edit users', false),
            new Permission(self::DELETE, 'Delete users', false),
            new Permission(self::IMPORT, 'Import users (LDAP)', false),
        ];
    }
}
