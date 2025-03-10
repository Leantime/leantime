<?php

/**
 * delClient Class - Deleting clients
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Services\Clients as ClientService;
    use Symfony\Component\HttpFoundation\Response;

    class DelClient extends Controller
    {
        private ClientService $clientService;

        /**
         * init - initialize private variables
         */
        public function init(
            ClientService $clientService
        )
        {
            $this->clientService = $clientService;
        }


        /**
         * get - display template
         */
        public function get($params):Response
        {
            // dd("In get function");
            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            //Only admins
            if (Auth::userIsAtLeast(Roles::$admin)) {
                if (isset($params['id']) === true) {
                    $id = (int) ($params['id']);

                    $this->tpl->assign('client', $this->clientService->get($id));

                    return $this->tpl->display('clients.delClient');
                } else {
                    return $this->tpl->display('errors.error403', responseCode: 403);
                }
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }


        /**
         * post - display template and delete client
         */
        public function post($params):Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            // Only admins
            if (Auth::userIsAtLeast(Roles::$admin)) {
                if (isset($params['id']) === true) {
                    $id = (int) ($params['id']);

                    if ($this->clientService->hasTickets($id) === true) {
                        $this->tpl->setNotification($this->language->__('notification.client_has_todos'), 'error');
                    } else {
                        if (isset($_POST['del']) === true) {
                            $this->clientService->delete($id);

                            $this->tpl->setNotification($this->language->__('notification.client_deleted'), 'success');

                            return Frontcontroller::redirect(BASE_URL.'/clients/showAll');
                        }
                    }

                    return $this->tpl->display('clients.delClient');
                } else {
                    return $this->tpl->display('errors.error403', responseCode: 403);
                }
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }
    }
}
