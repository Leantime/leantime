<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class moveTicket extends controller
    {
        private services\tickets $ticketService;

        public function init()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            $this->ticketService = new services\tickets();
            $this->projectService = new services\projects();

        }


        public function get($params)
        {

            $ticketId = $params['id'] ?? '';

            $ticket = $this->ticketService->getTicket($ticketId);

            $projects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id']);

            $this->tpl->assign('ticket', $ticket);
            $this->tpl->assign('projects', $projects);

            $this->tpl->displayPartial('tickets.moveTicket');

        }

        public function post($params)
        {
            $ticketId = null;
            if (isset($_GET['id'])) {
                $ticketId = (int)($_GET['id']);
            }

            $projectId = null;
            if(isset($params['projectId'])) {
                $projectId = (int)($params['projectId']);
            }

            if(!empty($ticketId) && !empty($projectId)){
                if($this->ticketService->moveTicket($ticketId, $projectId)) {
                    $this->tpl->setNotification($this->language->__("text.ticket_moved"), "success");
                }else{
                    $this->tpl->setNotification($this->language->__("text.move_problem"), "error");
                }
            }

            core\frontcontroller::redirect(BASE_URL."/tickets/moveTicket/".$ticketId."?closeModal=true");



        }
    }

}
