<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Auth\Services\Auth;

class DelGCal extends Controller
{
    private Calendar $calendarService;

    public function init(Calendar $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function run()
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
        if (isset($_GET['id']) === true) {
            $id = (int)($_GET['id']);
            $msgKey = '';
            if (isset($_POST['del']) === true) {
                $this->calendarService->deleteGCal($id);
                $msgKey = 'Kalender gelöscht';
            }
            $this->tpl->assign('msg', $msgKey);
            return $this->tpl->display('calendar.delGCal');
        } else {
            return $this->tpl->display('errors.error403');
        }
    }
}
