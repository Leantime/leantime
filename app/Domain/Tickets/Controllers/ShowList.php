<?php

namespace Leantime\Domain\Tickets\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

class ShowList extends Controller
{
    private TicketService $ticketService;

    public function init(
        TicketService $ticketService
    ): void {
        $this->ticketService = $ticketService;

        session(['lastPage' => CURRENT_URL]);
        session(['lastTicketView' => 'list']);
        session(['lastFilterdTicketListView' => CURRENT_URL]);
    }

    /**
     * @throws \Exception
     */
    public function get($params): Response
    {
        $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
        array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

        return $this->tpl->display('tickets.showList');
    }

    /**
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        // QuickAdd
        if (isset($_POST['quickadd'])) {
            $formParams = [
                'headline' => $_POST['headline'] ?? '',
                'milestone' => $_POST['milestone'] ?? '',
                'sprint' => $_POST['sprint'] ?? '',
                'projectId' => session('currentProject'),
                'editorId' => session('userdata.id'),
            ];

            $result = $this->ticketService->quickAddTicket($formParams);

            if (is_array($result)) {
                $this->tpl->setNotification($result['message'], $result['status']);
            }
        }

        return Frontcontroller::redirect(CURRENT_URL);
    }
}
