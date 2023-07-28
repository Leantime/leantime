<?php

/**
 * showAll Class - Show all clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showAll extends controller
    {
        private repositories\clients $clientRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(repositories\clients $clientRepo)
        {

            $this->clientRepo = $clientRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin], true);

            if ($_SESSION['userdata']['role'] == 'admin') {
                $this->tpl->assign('admin', true);
            }

            $this->tpl->assign('allClients', $this->clientRepo->getAll());
            $this->tpl->display('clients.showAll');
        }
    }
}
