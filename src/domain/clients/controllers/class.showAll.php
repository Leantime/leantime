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
            if(services\auth::userIsAtLeast("clientManager")) {

                if ($_SESSION['userdata']['role'] == 'admin') {

                    $tpl->assign('admin', true);

                }

                if(services\auth::userIsAtLeast("manager")) {
                    $tpl->assign('allClients', $clientRepo->getAll());
                }else{
                    $tpl->assign('allClients', array($clientRepo->getClient(services\auth::getUserClientId())));
                }

                $tpl->display('clients.showAll');

            } else {

                $tpl->display('general.error');

            }

        }

    }
}
