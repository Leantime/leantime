<?php

namespace leantime\domain\controllers {

    /**
     * importGCal Class - Add a new client
     *
     */

    use leantime\core;
    use leantime\domain\repositories;

    class importGCal
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
            $tpl->assign('helper', $helper);
            $tpl->assign('info', $msgKey);

            $tpl->display('calendar.importGCal');


        }

    }
}

