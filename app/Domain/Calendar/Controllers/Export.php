<?php

/**
 * showAll Class - show My Calender.
 */

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class Export extends Controller
{
    private Environment $config;
    private SettingRepository $settingsRepo;

    /**
     * init - initialize private variables.
     *
     * @param Environment       $config
     * @param SettingRepository $settingsRepo
     *
     * @return void
     */
    public function init(Environment $config, SettingRepository $settingsRepo): void
    {
        $this->config = $config;
        $this->settingsRepo = $settingsRepo;
    }

    /**
     * run - display template and edit data.
     *
     *
     * @return Response
     */
    public function run(): Response
    {
        if (isset($_GET['remove'])) {
            $this->settingsRepo->deleteSetting('usersettings.'.session('userdata.id').'.icalSecret');

            $this->tpl->setNotification('notifications.ical_removed_success', 'success');
        }

        //Add Post handling
        if (isset($_POST['generateUrl'])) {
            $uuid = Uuid::uuid4();
            $icalHash = $uuid->toString();

            $this->settingsRepo->saveSetting('usersettings.'.session('userdata.id').'.icalSecret', $icalHash);

            $this->tpl->setNotification('notifications.ical_success', 'success');
        }

        $icalHash = $this->settingsRepo->getSetting('usersettings.'.session('userdata.id').'.icalSecret');
        $userHash = hash('sha1', session('userdata.id').$this->config->sessionpassword);

        if (!$icalHash) {
            $icalUrl = '';
        } else {
            $icalUrl = BASE_URL.'/calendar/ical/'.$icalHash.'_'.$userHash;
        }

        //Add delete handling
        $this->tpl->assign('url', $icalUrl);

        return $this->tpl->displayPartial('calendar.export');
    }
}
