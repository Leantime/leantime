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

            $notificationService = new services\notifications();
            $notifications = array();
            $newnotificationCount = 0;
            if(isset($_SESSION['userdata'])){
                $notifications = $notificationService->getAllNotifications($_SESSION['userdata']['id']);
                $newnotificationCount = $notificationService->getAllNotifications($_SESSION['userdata']['id'], true);
            }
            $nCount = '';

            if (is_array($newnotificationCount)) {
                $nCount = count($newnotificationCount);
            }

            $this->tpl->assign('newNotificationCount', $nCount);
            $this->tpl->assign('notifications', $notifications);
            $this->tpl->assign('current', explode(".", core\frontcontroller::getCurrentRoute()));

            if(isset($_SESSION['userdata'])) {
                $this->tpl->assign("onTheClock", $this->timesheets->isClocked($_SESSION["userdata"]["id"]));
            }else{
                $this->tpl->assign("onTheClock", false);
            }
            $this->tpl->displayPartial("menu.headMenu");
        }
    }

}
