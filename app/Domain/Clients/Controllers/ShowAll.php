<?php

/**
 * showAll Class - Show all clients
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Services\Clients as ClientService;
    use Symfony\Component\HttpFoundation\Response;

    class ShowAll extends Controller
    {
        private ClientService $clientService;

        /**
         * init - initialize private variables
         */
        public function init(ClientService $clientService)
        {
            $this->clientService = $clientService;
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

            $clients = $this->clientService->getAll();

            $this->tpl->assign('allClients', $clients);

            return $this->tpl->display('clients.showAll');
        }
    }
}
