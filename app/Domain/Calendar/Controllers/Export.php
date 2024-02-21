<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace Leantime\Domain\Calendar\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Core\Environment;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Auth\Services\Auth;
    use Ramsey\Uuid\Uuid;

    /**
     *
     */
    class Export extends Controller
    {
        private Environment $config;
        private SettingRepository $settingsRepo;

        /**
         * init - initialize private variables
         */
        public function init(Environment $config, SettingRepository $settingsRepo)
        {
            $this->config = $config;
            $this->settingsRepo = $settingsRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            if (isset($_GET['remove'])) {
                $this->settingsRepo->deleteSetting("usersettings." . $_SESSION['userdata']['id'] . ".icalSecret");

                $this->tpl->setNotification("notifications.ical_removed_success", "success");
            }

            //Add Post handling
            if (isset($_POST['generateUrl'])) {
                $uuid = Uuid::uuid4();
                $icalHash = $uuid->toString();

                $this->settingsRepo->saveSetting("usersettings." . $_SESSION['userdata']['id'] . ".icalSecret", $icalHash);

                $this->tpl->setNotification("notifications.ical_success", "success");
            }

            $icalHash = $this->settingsRepo->getSetting("usersettings." . $_SESSION['userdata']['id'] . ".icalSecret");
            $userHash = hash('sha1', $_SESSION['userdata']['id'] . $this->config->sessionpassword);

            if (!$icalHash) {
                $icalUrl = "";
            } else {
                $icalUrl = BASE_URL . "/calendar/ical/" . $icalHash . "_" . $userHash;
            }

            //Add delete handling
            $this->tpl->assign("url", $icalUrl);

            return $this->tpl->displayPartial("calendar.export");
        }
    }

}
