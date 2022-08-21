<?php

/**
 * showAll Class - Show all clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showAll
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin], true);

            $tpl = new core\template();
            $clientRepo = new repositories\clients();

                if ($_SESSION['userdata']['role'] == 'admin') {

                    $tpl->assign('admin', true);

                }


                $tpl->assign('allClients', $clientRepo->getAll());


                $tpl->display('clients.showAll');



        }

    }
}
