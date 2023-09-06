<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Auth\Services\Auth;

    class MoveTicket extends Controller
    {
        private TicketService $ticketService;
        private ProjectService $projectService;

        public function init(
            TicketService $ticketService,
            ProjectService $projectService
        ) {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->ticketService = $ticketService;
            $this->projectService = $projectService;
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
            if (isset($params['projectId'])) {
                $projectId = (int)($params['projectId']);
            }

            if (!empty($ticketId) && !empty($projectId)) {
                if ($this->ticketService->moveTicket($ticketId, $projectId)) {
                    $this->tpl->setNotification($this->language->__("text.ticket_moved"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__("text.move_problem"), "error");
                }
            }

            FrontcontrollerCore::redirect(BASE_URL . "/tickets/moveTicket/" . $ticketId . "?closeModal=true");
        }
    }

}
