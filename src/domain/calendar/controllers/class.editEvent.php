<?php

/**
 * editEvent Class - Add a new client
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class editEvent
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $calendarRepo = new repositories\calendar();

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

                    if (isset($_POST['dateFrom']) === true && isset($_POST['timeFrom']) === true) {
                        $dateFrom = date('Y-m-d H:i:01', strtotime($_POST['dateFrom']." ".$_POST['timeFrom']));
                    }


                    if (isset($_POST['dateTo']) === true && isset($_POST['timeTo']) === true) {
                        $dateTo = date('Y-m-d H:i:01', strtotime($_POST['dateTo']." ".$_POST['timeTo']));
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
