<?php

/**
 * newClient Class - Add a new client
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Symfony\Component\HttpFoundation\Response;

    class NewClient extends Controller
    {
        private ClientRepository $clientRepo;

        private UserRepository $user;

        /**
         * init - initialize private variables
         */
        public function init(
            ClientRepository $clientRepo, 
            UserRepository $user
        ){
            $this->clientRepo = $clientRepo;
            $this->user = $user;
        }

        /**
         * get - display template and provide empty values object
         */
        public function get(): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            //Only admins
            if (Auth::userIsAtLeast(Roles::$admin)) {
                $values = (object) [
                    'name' => '',
                    'street' => '',
                    'zip' => '',
                    'city' => '',
                    'state' => '',
                    'country' => '',
                    'phone' => '',
                    'internet' => '',
                    'email' => '',
                ];

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
                $values = (object) [
                    'name' => '',
                    'street' => '',
                    'zip' => '',
                    'city' => '',
                    'state' => '',
                    'country' => '',
                    'phone' => '',
                    'internet' => '',
                    'email' => '',
                ];
                
                if (isset($_POST['save']) === true) {
                    $values = (object) [
                        'name' => ($params['name']),
                        'street' => ($params['street']),
                        'zip' => ($params['zip']),
                        'city' => ($params['city']),
                        'state' => ($params['state']),
                        'country' => ($params['country']),
                        'phone' => ($params['phone']),
                        'internet' => ($params['internet']),
                        'email' => ($params['email']),
                    ];

                    if ($values->name !== '') {
                        if ($this->clientRepo->isClient($values) !== true) {
                            $id = $this->clientRepo->addClient($values);
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
