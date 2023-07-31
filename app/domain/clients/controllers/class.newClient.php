<?php

/**
 * newClient Class - Add a new client
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class newClient extends controller
    {
        private repositories\clients $clientRepo;
        private repositories\users $user;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(repositories\clients $clientRepo, repositories\users $user)
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

            auth::authOrRedirect([roles::$owner, roles::$admin], true);

            //Only admins
            if (auth::userIsAtLeast(roles::$admin)) {
                $values = array(
                    'name' => '',
                    'street' => '',
                    'zip' => '',
                    'city' => '',
                    'state' => '',
                    'country' => '',
                    'phone' => '',
                    'internet' => '',
                    'email' => ''
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
                        'email' => ($_POST['email'])
                    );

                    if ($values['name'] !== '') {
                        if ($this->clientRepo->isClient($values) !== true) {
                            $id = $this->clientRepo->addClient($values);
                            $this->tpl->setNotification($this->language->__('notification.client_added_successfully'), 'success', 'new_client');
                            $this->tpl->redirect(BASE_URL . "/clients/showClient/" . $id);
                        } else {
                            $this->tpl->setNotification($this->language->__('notification.client_exists_already'), 'error');
                        }
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.client_name_not_specified'), 'error');
                    }
                }

                $this->tpl->assign('values', $values);
                $this->tpl->display('clients.newClient');
            } else {
                $this->tpl->display('errors.error403');
            }
        }
    }

}
