<?php

/**
 * newClient Class - Add a new client
 *
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;

    /**
     *
     */
    class NewClient extends Controller
    {
        private ClientRepository $clientRepo;
        private UserRepository $user;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(ClientRepository $clientRepo, UserRepository $user)
        {

            $this->clientRepo = $clientRepo;
            $this->user = $user;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            //Only admins
            if (Auth::userIsAtLeast(Roles::$admin)) {
                $values = array(
                    'name' => '',
                    'street' => '',
                    'zip' => '',
                    'city' => '',
                    'state' => '',
                    'country' => '',
                    'phone' => '',
                    'internet' => '',
                    'email' => '',
                );

                if (isset($_POST['save']) === true) {
                    $values = array(
                        'name' => ($_POST['name']),
                        'street' => ($_POST['street']),
                        'zip' => ($_POST['zip']),
                        'city' => ($_POST['city']),
                        'state' => ($_POST['state']),
                        'country' => ($_POST['country']),
                        'phone' => ($_POST['phone']),
                        'internet' => ($_POST['internet']),
                        'email' => ($_POST['email']),
                    );

                    if ($values['name'] !== '') {
                        if ($this->clientRepo->isClient($values) !== true) {
                            $id = $this->clientRepo->addClient($values);
                            $this->tpl->setNotification($this->language->__('notification.client_added_successfully'), 'success', 'new_client');
                            return Frontcontroller::redirect(BASE_URL . "/clients/showClient/" . $id);
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
