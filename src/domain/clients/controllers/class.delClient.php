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

            //Only admins
            if ($_SESSION['userdata']['role'] == 'admin') {

                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    $msgKey = '';

                    if ($clientRepo->hasTickets($id) === true) {

                        $msgKey = 'CLIENT_HAS_TICKETS';

                    } else {

                        if (isset($_POST['del']) === true) {

                            $clientRepo->deleteClient($id);

                            $msgKey = 'CLIENT_DELETED';

                        }

                    }

                    //Assign vars
                    $tpl->assign('client', $clientRepo->getClient($id));
                    $tpl->assign('msg', $msgKey);

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
