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
 * Consumers ({@see \Leantime\Domain\Reports\Services\ReportEngine} and any UI that
 * wants a Resources section) go through the registry and get null when no
 * provider is registered — the caller's graceful-degradation branch.
 *
 * The two methods mirror how the ReportEngine calls the rest of the report:
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
     * @return ResourceSummary Empty summary if none of the projects have
     *                         resources authored.
     */
    public function getForProjects(array $projectIds): ResourceSummary;

    /**
     * Aggregate resources for a program and its child projects.
     *
     * @param  int  $programId  A `zp_projects.id` where `type='program'`.
     */
    public function getForProgram(int $programId): ResourceSummary;
}
