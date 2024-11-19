<?php

/**
 * showAll Class - Show all clients
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Symfony\Component\HttpFoundation\Response;

    class ShowAll extends Controller
    {
        private ClientRepository $clientRepo;

        /**
         * init - initialize private variables
         */
        public function init(ClientRepository $clientRepo)
        {

            $this->clientRepo = $clientRepo;
        }

        /**
         * get - display template and edit data
         */
        public function get():Response
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            if (session('userdata.role') == 'admin') {
                $this->tpl->assign('admin', true);
            }

            $this->tpl->assign('allClients', $this->clientRepo->getAll());

            return $this->tpl->display('clients.showAll');
        }
    }
}
