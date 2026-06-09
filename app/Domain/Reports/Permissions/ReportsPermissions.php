<?php

namespace Leantime\Domain\Reports\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Reports permission vocabulary — a single project-scoped standard verb.
 *
 * Project reports (burndown charts, cumulative flow, ticket-status history) only aggregate data
 * a project member can already read item-by-item (tickets, sprints, milestones are all
 * readonly-visible), so the standard `view` verb is the sole capability and auto-grants to
 * readonly+ through the matrix in {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions}
 * with NO matrix edit. (Maintainer-approved loosening: the legacy /reports/show page gate was
 * editor+, which guarded nothing the lower roles couldn't already see.)
 *
 * The domain's system-level methods (cron ingestion and telemetry) are deliberately NOT part of
 * this vocabulary: they run with no session user and are not RPC-exposed (de-@api'd), so there is
 * nothing to grant.
 */
final class ReportsPermissions implements ProvidesPermissions
{
    /** View a project's reports (burndown, cumulative flow, status history). Readonly+. */
    public const VIEW = 'reports.view';

    public function domain(): string
    {
        return 'reports';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View project reports', true),
        ];
    }
}
