<?php

namespace leantime\domain\controllers {

    /**
     * importGCal Class - Add a new client
     *
     */

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class importGCal
    {

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

                $calendarRepo->addGUrl($values);

                $msgKey = 'Kalender hinzugefügt';


            }

            $tpl->assign('values', $values);
            $tpl->assign('info', $msgKey);

            $tpl->display('calendar.importGCal');


        }

    }
}

