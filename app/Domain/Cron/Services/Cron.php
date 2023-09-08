<?php

namespace Leantime\Domain\Cron\Services {

    use Leantime\Core\Environment;
    use Leantime\Domain\Audit\Repositories\Audit;
    use Leantime\Domain\Queue\Services\Queue;
    use PDO;

    class Cron
    {
        private Audit $AuditRepo;
        private Queue $queueSvc;
        private Environment $Environment;

        public function __construct(Audit $auditRepo, Queue $queueSvc, Environment $environment)
        {
            $this->auditRepo = $auditRepo;
            $this->queueSvc = $queueSvc;
            $this->environment = $environment;
        }

        public function runCron()
        {

            $lastEvent = $this->auditRepo->getLastEvent('cron');

            if (isset($lastEvent['date'])) {
                $lastCronEvent = strtotime($lastEvent['date']);
            } else {
                $lastCronEvent = 0;
            }

            // Using audit system to prevent too frequent executions
            $nowDate = time();
            $timeSince = abs($nowDate - $lastCronEvent);

            if ($timeSince < 300) {
                if ($this->environment->debug == true) {
                    error_log("Last cron execution was on " . $lastEvent['date'] . " plz come back later");
                }

                return false;
            }

            $this->auditRepo->storeEvent("cron", "Cron started");

            if ($this->environment->debug == true) {
                error_log("cron start");
            }

            $this->queueSvc->processQueue();

            if ($this->environment->debug == true) {
                error_log("cron end");
            }

            $this->auditRepo->pruneEvents();

            return true;
        }
    }
}
