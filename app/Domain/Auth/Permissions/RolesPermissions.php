<?php

namespace Leantime\Domain\Auth\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The roles permission vocabulary.
 *
 * MANAGE is GLOBAL-scoped (admin+ by default): create, change and delete roles and choose their
 * permissions. Core has no role-management screens; the custom roles plugin checks this.
 */
final class RolesPermissions implements ProvidesPermissions
{
    /** Create, change and delete roles and their permissions. Admin+ (global, company-wide). */
    public const MANAGE = 'roles.manage';

    public function domain(): string
    {
        return 'roles';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::MANAGE, 'Manage roles and permissions', false),
        ];
    }
}
