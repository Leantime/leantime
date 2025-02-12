<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Symfony\Component\HttpFoundation\Response;

    class ShowAll extends Controller
    {

        private TicketService $ticketService;


        public function init(
            TicketService $ticketService,

        ): void {

            $this->ticketService = $ticketService;

            session(['lastPage' => CURRENT_URL]);
            session(['lastTicketView' => 'table']);
            session(['lastFilterdTicketTableView' => CURRENT_URL]);

            if (! session()->exists('currentProjectName')) {
                Frontcontroller::redirect(BASE_URL . '/');
            }
        }

        /**
         * @throws \Exception
         */
        public function get($params): Response
        {
            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));
            $allTickets = $this->ticketService->getAllGrouped($template_assignments['searchCriteria']);
            $this->tpl->assign('allTickets', $allTickets);
            return $this->tpl->display('tickets.showAll');
        }
    }
}
