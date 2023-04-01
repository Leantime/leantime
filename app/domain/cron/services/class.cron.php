<?php

namespace leantime\domain\services {

    use leantime\core\environment;
    use leantime\domain\repositories\audit;

    class cron
    {


        private audit $auditRepo;
        private queue $queueSvc;

        public function __construct() {
            $this->auditRepo = new audit();
            $this->queueSvc = new queue();
        }


        public function runCron() {

            $lastEvent = $this->auditRepo->getLastEvent('cron');

            if(isset($lastEvent['date'])) {
                $lastCronEvent = strtotime($lastEvent['date']);
            }else{
                $lastCronEvent = 0;
            }

            // Using audit system to prevent too frequent executions
            $nowDate = time();
            $timeSince = abs($nowDate - $lastCronEvent);

            if ($timeSince < 300)
            {
                if(environment::getInstance()->debug == true) {
                    error_log("Last cron execution was on " . $lastEvent['date'] . " plz come back later");
                }

                return false;
            }

            $this->auditRepo->storeEvent("cron", "Cron started");

            if(environment::getInstance()->debug == true) {
                error_log("cron start");
            }

            $this->queueSvc->processQueue();

            if(environment::getInstance()->debug == true) {
                error_log( "cron end");
            }
            $this->auditRepo->pruneEvents();

            return true;


        }


    }
}
