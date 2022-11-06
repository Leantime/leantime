<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;
    use Ramsey\Uuid\Uuid;

    class export extends controller
    {

        private $config;
        private $settingsRepo;

        /**
         * init - initialize private variables
         */
        public function init()
        {

            $this->config = new core\config();
            $this->settingsRepo = new repositories\setting();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            if(isset($_GET['remove'])) {

                $this->settingsRepo->deleteSetting("usersettings.".$_SESSION['userdata']['id'].".icalSecret");

                $this->tpl->setNotification("notifications.ical_removed_success", "success");
            }

            //Add Post handling
            if(isset($_POST['generateUrl'])) {

                $uuid = Uuid::uuid4();
                $icalHash = $uuid->toString();

                $this->settingsRepo->saveSetting("usersettings.".$_SESSION['userdata']['id'].".icalSecret", $icalHash);

                $this->tpl->setNotification("notifications.ical_success", "success");

            }


            $icalHash = $this->settingsRepo->getSetting("usersettings.".$_SESSION['userdata']['id'].".icalSecret");
            $userHash = hash('sha1', $_SESSION['userdata']['id'].$this->config->sessionpassword);

            if($icalHash == false) {
                $icalUrl = "";
            }else{
                $icalUrl = BASE_URL."/calendar/ical/".$icalHash."_".$userHash;
            }



            //Add delete handling

            $this->tpl->assign("url", $icalUrl);

            $this->tpl->displayPartial("calendar.export");

            events::dispatch_event('end');

        }

    }

}
