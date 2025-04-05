<?php

namespace Leantime\Domain\Cron\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Audit\Repositories\Audit;
use Leantime\Domain\Queue\Services\Queue;
use Leantime\Domain\Reports\Services\Reports;

/**
 * @api
 */
class Cron
{
    use DispatchesEvents;

    private Audit $auditRepo;

    private Queue $queueSvc;

    private Environment $Environment;

    private Environment $environment;

    private Reports $reportService;

    private int $cronExecTimer = 60;

    public function __construct(Audit $auditRepo, Queue $queueSvc, Environment $environment, Reports $reportService)
    {
        $this->auditRepo = $auditRepo;
        $this->queueSvc = $queueSvc;
        $this->environment = $environment;
        $this->reportService = $reportService;
    }
}
