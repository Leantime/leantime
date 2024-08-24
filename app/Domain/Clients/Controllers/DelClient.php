<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;

    /**
     *
     */
    class DelClient extends Controller
    {
        private ClientRepository $clientRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {
            $this->clientRepo = app()->make(ClientRepository::class);
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
                if (isset($_GET['id']) === true) {
                    $id = (int)($_GET['id']);

                    if ($this->clientRepo->hasTickets($id) === true) {
                        $this->tpl->setNotification($this->language->__('notification.client_has_todos'), 'error');
                    } else {
                        if (isset($_POST['del']) === true) {
                            $this->clientRepo->deleteClient($id);

                            $this->tpl->setNotification($this->language->__('notification.client_deleted'), 'success');
                            return Frontcontroller::redirect(BASE_URL . "/clients/showAll");
                        }
                    }

                    $this->tpl->assign('client', $this->clientRepo->getClient($id));
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
