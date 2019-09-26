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
            $msgKey = '';
            $helper = new core\helper();

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

                        $dateFrom = $helper->date2timestamp($_POST['dateFrom'], $_POST['timeFrom']);
                        //                        $dateFrom = ''.($helper->timestamp2date($_POST['dateFrom'],6)).' '.($_POST['timeFrom']).'';    

                    }

                    if (isset($_POST['dateTo']) === true && isset($_POST['timeTo']) === true) {

                        $dateTo = $helper->date2timestamp($_POST['dateTo'], $_POST['timeTo']);
                        //                        $dateTo = ''.($helper->timestamp2date($_POST['dateTo'],6)).' '.($_POST['timeTo']).'';    

                    }


                    $values = array(
                        'description' => ($_POST['description']),
                        'dateFrom' => $dateFrom,
                        'dateTo' => $dateTo,
                        'allDay' => $allDay
                    );

                    if ($values['description'] !== '') {

                        if ($helper->validateTime($_POST['timeFrom']) === true) {

                            $calendarRepo->editEvent($values, $id);

                            $msgKey = 'Termin bearbeitet';

                        } else {
                            $msgKey = 'Zeit hat falsches Format hh:mm';
                        }

                    } else {

                        $msgKey = 'Keine Beschreibung angegeben';

                    }


                }

                $tpl->assign('values', $values);
                $tpl->assign('helper', $helper);
                $tpl->assign('info', $msgKey);

                $tpl->display('calendar.editEvent');

            } else {

                $tpl->display('general.error');

            }

        }

    }

}
