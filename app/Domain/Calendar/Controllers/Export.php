<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Export extends Controller
{
    private Environment $config;
    private SettingRepository $settingsRepo;

    private Calendar $calendarService;

    /**
     * init - initialize private variables
     *
     * @param Environment       $config
     * @param SettingRepository $settingsRepo
     *
     * @return void
     */
    public function init(
        Environment $config,
        SettingRepository $settingsRepo,
        Calendar $calendarService
     ): void
    {
        $this->config = $config;
        $this->settingsRepo = $settingsRepo;
        $this->calendarService = $calendarService;
    }

    /**
     * run - display template and edit data
     *
     * @access public
     *
     * @return Response
     */
    public function run(): Response
    {
        if (isset($_GET['remove'])) {

            $this->settingsRepo->deleteSetting("usersettings." . session("userdata.id") . ".icalSecret");
            $this->tpl->setNotification("notifications.ical_removed_success", "success");

        }

        //Add Post handling
        if (isset($_POST['generateUrl'])) {

            try {
                $this->calendarService->generateIcalHash();
                $this->tpl->setNotification("notifications.ical_success", "success");
            }catch(\Exception $e) {
                $this->tpl->setNotification("There was a problem generating the ical hash", "error");
            }

        }

        $icalUrl = "";
        try {
            $icalUrl = $this->calendarService->getICalUrl();
        }catch(\Exception $e) {
            $this->tpl->setNotification("Could not find ical URL", "error");
        }

        //Add delete handling
        $this->tpl->assign("url", $icalUrl);

        return $this->tpl->displayPartial("calendar.export");
    }
}
