<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delClient extends controller
    {
        private repositories\clients $clientRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {
            $this->clientRepo = app()->make(repositories\clients::class);
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
                if (isset($_GET['id']) === true) {
                    $id = (int)($_GET['id']);

                    if ($this->clientRepo->hasTickets($id) === true) {
                        $this->tpl->setNotification($this->language->__('notification.client_has_todos'), 'error');
                    } else {
                        if (isset($_POST['del']) === true) {
                            $this->clientRepo->deleteClient($id);

                            $this->tpl->setNotification($this->language->__('notification.client_deleted'), 'success');
                            $this->tpl->redirect(BASE_URL . "/clients/showAll");
                        }
                    }

                    $this->tpl->assign('client', $this->clientRepo->getClient($id));
                    $this->tpl->display('clients.delClient');
                } else {
                    $this->tpl->display('errors.error403');
                }
            } else {
                $this->tpl->display('errors.error403');
            }
        }
    }
}
