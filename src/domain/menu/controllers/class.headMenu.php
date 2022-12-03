<?php
namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\services;

    class headMenu extends controller
    {

        private $timesheets;

        public function init()
        {

            $this->timesheets = new services\timesheets();

        }

        public function run()
        {

            $this->tpl->assign('current', explode(".", core\frontcontroller::getCurrentRoute()));
            $this->tpl->assign("onTheClock", $this->timesheets->isClocked($_SESSION["userdata"]["id"]));
            $this->tpl->displayPartial("menu.headMenu");

        }

    }

}
