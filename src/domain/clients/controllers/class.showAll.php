<?php

/**
 * showAll Class - Show all clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class showAll
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $clientRepo = new repositories\clients();

            //Only admins and employees
            if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'employee') {

                if ($_SESSION['userdata']['role'] == 'admin') {

                    $tpl->assign('admin', true);

                }

                $tpl->assign('allClients', $clientRepo->getAll());

                $tpl->display('clients.showAll');

            } else {

                $tpl->display('general.error');

            }

        }

    }
}
