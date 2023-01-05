<?php

namespace leantime\domain\controllers {

    /**
     * importGCal Class - Add a new client
     *
     */

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class importGCal extends controller
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

            $msgKey = '';

            $values = array(
                'url' => '',
                'name' => '',
                'colorClass' => ''
            );

            if (isset($_POST['save']) === true) {

                $values = array(
                    'url' => ($_POST['url']),
                    'name' => ($_POST['name']),
                    'colorClass' => ($_POST['color'])
                );

                $this->calendarRepo->addGUrl($values);

                $msgKey = 'Kalender hinzugefügt';

            }

            $this->tpl->assign('values', $values);
            $this->tpl->assign('info', $msgKey);

            $this->tpl->display('calendar.importGCal');

        }

    }
}

