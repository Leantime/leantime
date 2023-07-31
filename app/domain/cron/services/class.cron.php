<?php

namespace leantime\domain\services {

    use leantime\core\environment;
    use leantime\domain\repositories\audit;

    class cron
    {
        private audit $auditRepo;
        private queue $queueSvc;
        private environment $environment;

        public function __construct(audit $auditRepo, queue $queueSvc, environment $environment)
        {
            $this->auditRepo = $auditRepo;
            $this->queueSvc = $queueSvc;
            $this->environment = $environment;
        }

        public function runCron() {

            $lastEvent = $this->auditRepo->getLastEvent('cron');

            if (isset($lastEvent['date'])) {
                $lastCronEvent = strtotime($lastEvent['date']);
            } else {
                $lastCronEvent = 0;
            }

            // Using audit system to prevent too frequent executions
            $nowDate = time();
            $timeSince = abs($nowDate - $lastCronEvent);

            if ($timeSince < 300)
            {
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
                error_log( "cron end");
            }

            $this->auditRepo->pruneEvents();

            return true;
        }
    }
}
