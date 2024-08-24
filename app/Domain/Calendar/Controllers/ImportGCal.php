<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * importGCal Class - Add a new client
 */
class ImportGCal extends Controller
{
    private CalendarRepository $calendarRepo;

    /**
     * init - initialize private variables
     *
     * @param CalendarRepository $calendarRepo
     *
     * @return void
     */
    public function init(CalendarRepository $calendarRepo): void
    {
        $this->calendarRepo = $calendarRepo;
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
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $values = array(
            'url' => '',
            'name' => '',
            'colorClass' => '',
        );

        if (isset($_POST['name']) === true) {
            $values = array(
                'url' => ($_POST['url']),
                'name' => ($_POST['name']),
                'colorClass' => ($_POST['colorClass']),
            );

            $this->calendarRepo->addGUrl($values);
            $this->tpl->setNotification('notification.gcal_imported_successfully', 'success', 'externalcalendar_created');
        }

        $this->tpl->assign('values', $values);

        return $this->tpl->displayPartial('calendar.importGCal');
    }
}
