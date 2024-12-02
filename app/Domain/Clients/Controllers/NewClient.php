<?php

/**
 * newClient Class - Add a new client
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Models\Clients as ClientModel;
    use Leantime\Domain\Clients\Services\Clients as ClientService;
    use Symfony\Component\HttpFoundation\Response;

    class NewClient extends Controller
    {
        private ClientService $clientService;

        /**
         * init - initialize private variables
         */
        public function init(
            ClientService $clientService 
        ){
            $this->clientService = $clientService;
        }

        /**
         * get - display template and provide empty values object
         */
        public function get(): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            //Only admins
            if (Auth::userIsAtLeast(Roles::$admin)) {

                $values = app() -> make(ClientModel::class);
            
                $this->tpl->assign('values', $values);

                return $this->tpl->display('clients.newClient');
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }

        /**
         * post - display template and save data
         */
        public function post($params): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            //Only admins
            if (Auth::userIsAtLeast(Roles::$admin)) {
                
                $values = null;

                if (isset($params['save']) === true) {

                    $values = app() -> make(ClientModel::class, ['attributes' => $params]);
                    if ($values->name !== '') {
                        if ($this->clientService->isClient($values) !== true) {
                            $id = $this->clientService->create($values);
                            $this->tpl->setNotification($this->language->__('notification.client_added_successfully'), 'success', 'new_client');

                            return Frontcontroller::redirect(BASE_URL.'/clients/showClient/'.$id);
                        } else {
                            $this->tpl->setNotification($this->language->__('notification.client_exists_already'), 'error');
                        }
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.client_name_not_specified'), 'error');
                    }
                }

                $this->tpl->assign('values', $values);

                return $this->tpl->display('clients.newClient');
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }
    }

}
