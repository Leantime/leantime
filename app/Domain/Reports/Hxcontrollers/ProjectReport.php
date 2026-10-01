<?php

declare(strict_types=1);

namespace Leantime\Domain\Reports\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Reports\Models\ReportPeriod;
use Leantime\Domain\Reports\Services\ReportEngine;

/**
 * Re-renders the project report body when the period filter changes (the page shell with
 * header and period picker stays in place).
 */
class ProjectReport extends HtmxController
{
    protected static string $view = 'reports::partials.projectReportBody';

    private ReportEngine $reportEngine;

    public function init(ReportEngine $reportEngine): void
    {
        $this->reportEngine = $reportEngine;
    }

    /**
     * Builds the report body for the requested period. Authorization happens in the engine
     * (reports.view per project) — an unauthorized project yields an empty report.
     */
    public function get(): void
    {
        $projectId = (int) session('currentProject');
        $period = ReportPeriod::fromRequest($this->incomingRequest->query->all());

        $this->tpl->assign('projectId', $projectId);
        $this->tpl->assign('period', $period);
        $this->tpl->assign('report', $this->reportEngine->buildReport([$projectId], $period));
    }
}
