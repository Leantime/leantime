<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Services\Calendar;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class EditExternal extends Controller
{
    private Calendar $calendarService;

    /**
     * @param Calendar $calendarService
     *
     * @return void
     */
    public function init(Calendar $calendarService): void
    {
        $this->calendarService = $calendarService;
    }

    /**
     * @return Response
     *
     * @throws \Exception
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        if (isset($_GET['id']) === true) {
            $id = ($_GET['id']);

            $calendar = $this->calendarService->getExternalCalendar($id, session("userdata.id"));

            $values = $calendar;

            if (isset($_POST['save']) === true) {
                $values = array(
                    'id' => ($calendar['id']),
                    'url' => ($_POST['url']),
                    'name' => ($_POST['name']),
                    'colorClass' => ($_POST['colorClass']),
                );

                $this->calendarService->editExternalCalendar($values, $id);

                $this->tpl->setNotification("notification.external_calendar_edited", "success", "externalCalendar_edited");
            }

            $this->tpl->assign('values', $values);

            return $this->tpl->displayPartial('calendar.editExternalCalendar');
        } else {
            return $this->tpl->display('errors.error403');
        }
    }
}
