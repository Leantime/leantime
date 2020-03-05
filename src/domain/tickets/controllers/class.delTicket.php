<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delTicket
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $ticketRepo = new repositories\tickets();

            //Only admins
            if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' || $_SESSION['userdata']['role'] == 'developer') {

                if (isset($_GET['id'])) {
                    $id = (int)($_GET['id']);
                }

                $msgKey = '';

                if (isset($_POST['del'])) {


                    $ticketRepo->delTicket($id);

                    $msgKey = 'TICKET_DELETED';

                }

                $tpl->assign('info', $msgKey);
                $tpl->assign('ticket', $ticketRepo->getTicket($id));

                $tpl->display('tickets.delTicket');

            } else {

                $tpl->display('general.error');

            }

        }

    }

}
