<?php

declare(strict_types=1);

namespace Leantime\Core\Resources\Contracts;

use Leantime\Core\Resources\Models\ResourceSummary;

/**
 * Contract for a plugin that provides Resources data (people allocations, budget
 * lines, dependencies) to the rest of the app.
 *
 * Core does not implement this — a plugin (currently PgmPro) registers itself as
 * *the* provider via {@see \Leantime\Core\Resources\Services\ResourcesRegistry}.
 * Consumers (the stakeholder report's Resources section and any UI that wants
 * a Resources block) go through the registry and get null when no provider is
 * registered — the caller's graceful-degradation branch.
 *
 * The two methods mirror how a report calls the rest of its data providers:
 *   - by explicit project id set (project read-out, plan read-out)
 *   - by program root (used when a caller has a program id and wants the
 *     canonical program-scoped roll-up including provider-specific caching)
 *
 * A provider MAY implement getForProgram() as a thin wrapper over
 * getForProjects() with the program's descendants pre-resolved.
 */
interface ResourcesGateway
{
    /**
     * Aggregate resources across an explicit project set.
     *
     * @param  array<int>  $projectIds  Projects to aggregate over. May include
     *                                  the program row itself and its children,
     *                                  or a hand-picked subset for a report.
     * @param  string|null  $actualsFrom  Start of the window for actual logged
     *                                    hours (Y-m-d, DB timezone). When null,
     *                                    the provider's default window applies
     *                                    (current week). Dates are primitives on
     *                                    purpose — core must not depend on the
     *                                    reports domain's ReportPeriod.
     * @param  string|null  $actualsTo  End of the actuals window (Y-m-d).
     * @return ResourceSummary Empty summary if none of the projects have
     *                         resources authored.
     */
    public function getForProjects(array $projectIds, ?string $actualsFrom = null, ?string $actualsTo = null): ResourceSummary;

    /**
     * Aggregate resources for a program and its child projects.
     *
     * @param  int  $programId  A `zp_projects.id` where `type='program'`.
     * @param  string|null  $actualsFrom  See getForProjects().
     * @param  string|null  $actualsTo  See getForProjects().
     */
    public function getForProgram(int $programId, ?string $actualsFrom = null, ?string $actualsTo = null): ResourceSummary;
}
