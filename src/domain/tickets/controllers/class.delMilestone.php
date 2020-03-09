<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\services;

    class delMilestone
    {

        private $ticketService;
        private $tpl;
        private $language;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->language = new core\language();
            $this->ticketService = new services\tickets();

        }


        public function get()
        {

            //Only admins
            if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager') {

                if (isset($_GET['id'])) {
                    $id = (int)($_GET['id']);
                }

                $this->tpl->assign('ticket', $this->ticketService->getTicket($id));
                $this->tpl->displayPartial('tickets.delMilestone');

            } else {

                $this->tpl->displayPartial('general.error');

            }

        }

        public function post($params) {

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            //Only admins
            if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager') {

                if (isset($params['del'])) {

                    $result = $this->ticketService->deleteMilestone($id);

                    if($result === true) {
                        $this->tpl->setNotification($this->language->__("notification.milestone_deleted"), "success");
                        $this->tpl->redirect(BASE_URL."/tickets/roadmap");
                    }else{
                        $this->tpl->setNotification($this->language->__($result['msg']), "error");
                        $this->tpl->assign('ticket', $this->ticketService->getTicket($id));
                        $this->tpl->displayPartial('tickets.delMilestone');
                    }

                }else{
                    $this->tpl->displayPartial('general.error');
                }

            }else{
                $this->tpl->displayPartial('general.error');
            }
        }

    }

}
