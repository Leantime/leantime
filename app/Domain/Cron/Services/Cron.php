<?php

namespace Leantime\Domain\Cron\Services {

    use Leantime\Core\Environment;
    use Leantime\Domain\Audit\Repositories\Audit;
    use Leantime\Domain\Queue\Services\Queue;
    use PDO;
    use PHPMailer\PHPMailer\Exception;

    /**
     *
     */
    class Cron
    {
        private Audit $auditRepo;
        private Queue $queueSvc;
        private Environment $Environment;
        private Environment $environment;

        /**
         * @param Audit       $auditRepo
         * @param Queue       $queueSvc
         * @param Environment $environment
         */
        public function __construct(Audit $auditRepo, Queue $queueSvc, Environment $environment)
        {
            $this->auditRepo =  $auditRepo;
            $this->queueSvc = $queueSvc;
            $this->environment = $environment;
        }

        /**
         * @return bool
         * @throws Exception
         */
        public function runCron(): bool
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

            //Run cron max every 60 seconds
            if ($timeSince < 60) {
                if ($this->environment->debug) {
                    error_log("Last cron execution was on " . $lastEvent['date'] . " plz come back later");
                }

                return false;
            }



            //Process Queue
            $this->queueSvc->processQueue();

            //Process Audit Table
            $this->auditRepo->pruneEvents();

            //Process other events
            self::dispatchEvents("cronJob", $lastEvent);

            return true;
        }
    }
}
