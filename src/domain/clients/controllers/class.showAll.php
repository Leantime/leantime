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
            if(core\login::userIsAtLeast("clientManager")) {

                if ($_SESSION['userdata']['role'] == 'admin') {

                    $tpl->assign('admin', true);

                }

                if(core\login::userIsAtLeast("manager")) {
                    $tpl->assign('allClients', $clientRepo->getAll());
                }else{
                    $tpl->assign('allClients', array($clientRepo->getClient(core\login::getUserClientId())));
                }

                $tpl->display('clients.showAll');

            } else {

                $tpl->display('general.error');

            }

        }

    }
}
