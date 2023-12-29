<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Auth\Services\Auth;

class EditExternal extends Controller
{
    private Calendar $calendarService;

    public function init(Calendar $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function run()
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $msgKey = '';

        if (isset($_GET['id']) === true) {
            $id = ($_GET['id']);

            $calendar = $this->calendarService->getExternalCalendar($id, $_SESSION['userdata']['id']);

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
