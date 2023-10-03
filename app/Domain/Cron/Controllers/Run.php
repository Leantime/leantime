<?php

namespace Leantime\Domain\Cron\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Cron\Services\Cron;
    use PDO;

    /**
     *
     */

    /**
     *
     */
    class Run extends Controller
    {
        private Cron $cronSvc;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(Cron $cronSvc)
        {
            $this->cronSvc = $cronSvc;
        }

        /**
         * @return void
         */
        public function run()
        {
            $this->cronSvc->runCron();
        }
    }
}
