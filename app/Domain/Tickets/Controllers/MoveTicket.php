<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class MoveTicket extends Controller
    {
        private TicketService $ticketService;
        private ProjectService $projectService;

        /**
         * @param TicketService  $ticketService
         * @param ProjectService $projectService
         * @return void
         */
        public function init(
            TicketService $ticketService,
            ProjectService $projectService
        ): void {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->ticketService = $ticketService;
            $this->projectService = $projectService;
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function get($params): Response
        {
            $ticketId = $params['id'] ?? '';

            $ticket = $this->ticketService->getTicket($ticketId);

            $projects = $this->projectService->getProjectsAssignedToUser(session("userdata.id"));

            $this->tpl->assign('ticket', $ticket);
            $this->tpl->assign('projects', $projects);

            return $this->tpl->displayPartial('tickets.moveTicket');
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {
            if (! empty($ticketId = (int) $_GET['id'] ?? null) && ! empty($projectId = (int) $params['projectId'] ?? null)) {
                if ($this->ticketService->moveTicket($ticketId, $projectId)) {
                    $this->tpl->setNotification($this->language->__("text.ticket_moved"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__("text.move_problem"), "error");
                }
            }

            return FrontcontrollerCore::redirect(BASE_URL . "/tickets/moveTicket/" . $ticketId . "?closeModal=true");
        }
    }

}
