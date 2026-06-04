<?php

namespace Leantime\Domain\Timesheets\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Timesheets permission vocabulary — company-wide (GLOBAL-scoped) verbs.
 *
 * Timesheets are GLOBAL-scoped (projectScoped = false): they represent the time an employee logs
 * across the whole company, and the controllers have always gated on the user's GLOBAL role
 * (Auth::authOrRedirect([...], forceGlobalRoleCheck: true)). So these verbs resolve against the
 * global role, not a project role.
 *
 * Two verb families:
 *  - view/create/edit/delete — EDITOR+ for the user's OWN time (ownership is enforced in the
 *    service: a non-manager may only read/write their own entries).
 *  - manage — MANAGER+ only: act on OTHER users' time, mark invoiced/paid, and the cross-user/
 *    cross-project reports.
 *
 * Because these are GLOBAL-scoped, the standard verbs do NOT auto-grant through the project-scoped
 * matrix rules — {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions} grants editor the
 * four standard keys and manager the `manage` key explicitly (admin/owner auto-grant via scope:any).
 */
final class TimesheetsPermissions implements ProvidesPermissions
{
    /** View own timesheets; managers view anyone's. Ownership enforced in the service. */
    public const VIEW = 'timesheets.view';

    /** Log time for own account; managers log for any user. Ownership enforced in the service. */
    public const CREATE = 'timesheets.create';

    /** Edit own timesheets; managers edit anyone's. Ownership enforced in the service. */
    public const EDIT = 'timesheets.edit';

    /** Delete own timesheets; managers delete anyone's. Ownership enforced in the service. */
    public const DELETE = 'timesheets.delete';

    /** Manage timesheets company-wide: mark invoiced/paid, cross-user reports, act on others' time. Manager+. */
    public const MANAGE = 'timesheets.manage';

    public function domain(): string
    {
        return 'timesheets';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View own timesheets (anyone\'s when manager+)', false),
            new Permission(self::CREATE, 'Log time for own account (others when manager+)', false),
            new Permission(self::EDIT, 'Edit own timesheets (others when manager+)', false),
            new Permission(self::DELETE, 'Delete own timesheets (others when manager+)', false),
            new Permission(self::MANAGE, 'Manage timesheets company-wide (invoice/pay/reports)', false),
        ];
    }
}
