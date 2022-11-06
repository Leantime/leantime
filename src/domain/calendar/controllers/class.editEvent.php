<?php

/**
 * editEvent Class - Add a new client
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class editEvent extends controller
    {

        private $calendarRepo;

        /**
         * init - initialize private variables
         */
        public function init()
        {

            $this->calendarRepo = new repositories\calendar();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            if (isset($_GET['id']) === true) {

                $id = ($_GET['id']);

                $row = $this->calendarRepo->getEvent($id);

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

                        $this->calendarRepo->editEvent($values, $id);

                        $this->tpl->setNotification('notification.event_edited_successfully', 'success');

                    } else {

                        $this->tpl->setNotification('notification.please_enter_title', 'error');

                    }

                }

                $this->tpl->assign('values', $values);
                $this->tpl->display('calendar.editEvent');

            } else {

                $this->tpl->display('general.error');

            }

        }

    }

}
