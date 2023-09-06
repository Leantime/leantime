<?php

namespace Leantime\Domain\Cron\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Cron\Services\Cron;
    use PDO;

    class Run extends Controller
    {
        private Cron $CronSvc;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(Cron $CronSvc)
        {
            $this->cronSvc = $cronSvc;
        }

        public function run()
        {
            $this->cronSvc->runCron();
        }
    }
}
