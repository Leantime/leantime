<?php
namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\services;

    class headMenu
    {

        private $tpl;
        private $timesheets;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->timesheets = new services\timesheets();
        }

        public function run()
        {

            $this->tpl->assign("onTheClock", $this->timesheets->isClocked($_SESSION["userdata"]["id"]));
            $this->tpl->displayPartial("general.headMenu");

        }

    }
}