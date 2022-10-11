<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;
    use Ramsey\Uuid\Uuid;

    class export
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {


            $tpl = new core\template();
            $config = new core\config();
            $settingsRepo = new repositories\setting();

            if(isset($_GET['remove'])) {

                $settingsRepo->deleteSetting("usersettings.".$_SESSION['userdata']['id'].".icalSecret");

                $tpl->setNotification("notifications.ical_removed_success", "success");
            }

            //Add Post handling
            if(isset($_POST['generateUrl'])) {

                $uuid = Uuid::uuid4();
                $icalHash = $uuid->toString();

                $settingsRepo->saveSetting("usersettings.".$_SESSION['userdata']['id'].".icalSecret", $icalHash);

                $tpl->setNotification("notifications.ical_success", "success");

            }


            $icalHash = $settingsRepo->getSetting("usersettings.".$_SESSION['userdata']['id'].".icalSecret");
            $userHash = hash('sha1', $_SESSION['userdata']['id'].$config->sessionpassword);

            if($icalHash == false) {
                $icalUrl = "";
            }else{
                $icalUrl = BASE_URL."/calendar/ical/".$icalHash."_".$userHash;
            }



            //Add delete handling

            $tpl->assign("url", $icalUrl);

            $tpl->displayPartial("calendar.export");

        }

    }

}
