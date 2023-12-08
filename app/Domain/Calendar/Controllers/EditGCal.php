<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Auth\Services\Auth;

class EditGCal extends Controller
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

            $calendar = $this->calendarService->getGCalendar($id);

            $values = array(
                'url' => $calendar['url'],
                'name' => $calendar['name'],
                'colorClass' => $calendar['colorClass'],
            );

            if (isset($_POST['save']) === true) {
                $values = array(
                    'url' => ($_POST['url']),
                    'name' => ($_POST['name']),
                    'colorClass' => ($_POST['color']),
                );

                $this->calendarService->editGCalendar($values, $id);

                $msgKey = 'Kalender bearbeitet';
            }

            $this->tpl->assign('values', $values);
            $this->tpl->assign('info', $msgKey);
            $this->tpl->display('calendar.editGCal');
        } else {
            $this->tpl->display('errors.error403');
        }
    }
}
