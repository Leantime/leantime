<?php

/**
 * newClient Class - Add a new client
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class newClient
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $clientRepo = new repositories\clients();
            $user = new repositories\users();

            //Only admins
            if ($user->isAdmin($_SESSION['userdata']['id'])) {

                $msgKey = '';

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
                        if ($clientRepo->isClient($values) !== true) {

                            $id = $clientRepo->addClient($values);
                            $tpl->setNotification('ADD_CLIENT_SUCCESS', 'success');
                            $tpl->redirect(BASE_URL."/clients/showClient/".$id);

                        } else {

                            $tpl->setNotification('CLIENT_EXISTS', 'error');
                        }
                    } else {

                        $tpl->setNotification('NO_NAME', 'error');
                    }


                }

                $tpl->assign('values', $values);
                $tpl->display('clients.newClient');
            } else {

                $tpl->display('general.error');

            }

        }

    }

}
