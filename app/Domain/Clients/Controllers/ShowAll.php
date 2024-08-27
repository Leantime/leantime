<?php

/**
 * showAll Class - Show all clients
 *
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;

    /**
     *
     */
    class ShowAll extends Controller
    {
        private ClientRepository $clientRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(ClientRepository $clientRepo)
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

            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            if (session("userdata.role") == 'admin') {
                $this->tpl->assign('admin', true);
            }

            $this->tpl->assign('allClients', $this->clientRepo->getAll());
            return $this->tpl->display('clients.showAll');
        }
    }
}
