<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delClient
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
            $language = new core\language();

            //Only admins
            if(core\login::userIsAtLeast("clientManager")) {

                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    if ($clientRepo->hasTickets($id) === true) {

                        $tpl->setNotification($language->__('notification.client_has_todos'), 'error');

                    } else {

                        if (isset($_POST['del']) === true) {

                            $clientRepo->deleteClient($id);

                            $tpl->setNotification($language->__('notification.client_deleted'), 'success');
                            $tpl->redirect(BASE_URL."/clients/showAll");

                        }

                    }

                    $tpl->assign('client', $clientRepo->getClient($id));
                    $tpl->display('clients.delClient');

                } else {

                    $tpl->display('general.error');

                }

            } else {

                $tpl->display('general.error');

            }

        }

    }
}
