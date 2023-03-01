<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class delMilestone extends controller
    {
        private $ticketService;

        public function init()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            $this->ticketService = new services\tickets();
        }


        public function get()
        {

            //Only admins
            if (auth::userIsAtLeast(roles::$editor)) {
                if (isset($_GET['id'])) {
                    $id = (int)($_GET['id']);
                }

                $this->tpl->assign('ticket', $this->ticketService->getTicket($id));
                $this->tpl->displayPartial('tickets.delMilestone');
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }

        public function post($params)
        {

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            //Only admins
            if (auth::userIsAtLeast(roles::$editor)) {
                if (isset($params['del'])) {
                    $result = $this->ticketService->deleteMilestone($id);

                    if ($result === true) {
                        $this->tpl->setNotification($this->language->__("notification.milestone_deleted"), "success");
                        $this->tpl->redirect(BASE_URL . "/tickets/roadmap");
                    } else {
                        $this->tpl->setNotification($this->language->__($result['msg']), "error");
                        $this->tpl->assign('ticket', $this->ticketService->getTicket($id));
                        $this->tpl->displayPartial('tickets.delMilestone');
                    }
                } else {
                    $this->tpl->displayPartial('errors.error403');
                }
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }
    }

}
