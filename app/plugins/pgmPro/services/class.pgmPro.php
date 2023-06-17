<?php

namespace leantime\plugins\services {

    use leantime\core;
    use leantime\domain\repositories;
    use DatePeriod;
    use DateTime;
    use DateInterval;
    use leantime\domain\services\comments;
    use leantime\domain\services\projects;
    use leantime\domain\services\reports;
    use leantime\domain\services\tickets;

    class pgmPro
    {
        private repositories\projects $projectRepository;
        private \leantime\plugins\repositories\pgmPro\programs $programRepository;

        public function __construct()
        {
            $this->tpl = new core\template();
        }

        public function install()
        {
            $this->tpl = new core\template();
        }

        public function update()
        {
            $this->tpl = new core\template();
        }


    }
}
