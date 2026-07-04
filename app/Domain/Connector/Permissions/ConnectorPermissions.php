<?php

namespace Leantime\Domain\Connector\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Connector (integrations / import) permission vocabulary — the verbs only.
 *
 * Connector integrations hold stored third-party credentials and drive data import, so reading,
 * creating, editing, and deleting them is an installation-wide administrative capability. The
 * single verb below is COMPANY-WIDE (`projectScoped = false`); call sites gate with
 * `#[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]`, which by the default role
 * map lands on admin/owner only.
 */
final class ConnectorPermissions implements ProvidesPermissions
{
    /** Read, create, edit, delete, or import connector integrations (company-wide). */
    public const MANAGE = 'connector.manage';

    public function domain(): string
    {
        return 'connector';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::MANAGE, 'Manage integrations', false),
        ];
    }
}
