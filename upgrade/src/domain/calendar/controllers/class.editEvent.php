<?php

/**
 * editEvent Class - Add a new client
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class editEvent
    {

        public $language;
        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            $tpl = new core\template();
            $calendarRepo = new repositories\calendar();
            $this->language = new core\language();

            if (isset($_GET['id']) === true) {

                $id = ($_GET['id']);

                $row = $calendarRepo->getEvent($id);

                $values = array(
                    'description' => $row['description'],
                    'dateFrom' => $row['dateFrom'],
                    'dateTo' => $row['dateTo'],
                    'allDay' => $row['allDay']
                );


                if (isset($_POST['save']) === true) {

                    if (isset($_POST['allDay']) === true) {

                        $allDay = 'true';

                    } else {

                        $allDay = 'false';

                    }

                    $dateFrom = null;
                    if (isset($_POST['dateFrom']) === true && isset($_POST['timeFrom']) === true) {
                        $dateFrom = $this->language->getISODateTimeString($_POST['dateFrom']." ".$_POST['timeFrom']);
                    }

                    $dateTo = null;
                    if (isset($_POST['dateTo']) === true && isset($_POST['timeTo']) === true) {
                        $dateTo =  $this->language->getISODateTimeString($_POST['dateTo']." ".$_POST['timeTo']);
                    }

                    $values = array(
                        'description' => ($_POST['description']),
                        'dateFrom' => $dateFrom,
                        'dateTo' => $dateTo,
                        'allDay' => $allDay
                    );

                    if ($values['description'] !== '') {

                        $calendarRepo->editEvent($values, $id);

                        $tpl->setNotification('notification.event_edited_successfully', 'success');

                    } else {

                        $tpl->setNotification('notification.please_enter_title', 'error');

                    }

                }

                $tpl->assign('values', $values);
                $tpl->display('calendar.editEvent');

            } else {

                $tpl->display('general.error');

            }

        }

    }

}
