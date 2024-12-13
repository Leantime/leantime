<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Projects\Services\Projects;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Core\Support\FromFormat;


class ShowTicket extends HtmxController
{
    protected static string $view = 'tickets::components.ticket-column';

    private Tickets $ticketService;
    private Projects $projectService;
    private Timesheets $timesheetService;
    /**
     * Controller constructor
     *
     * @param  Timesheets  $timesheetService
     */
    public function init(Tickets $ticketService, Projects $projectService, Timesheets $timesheetService): void
    {
        $this->ticketService = $ticketService;
        $this->projectService = $projectService;
        $this->timesheetService = $timesheetService;
    }

    // public function post($params): Response
    // {
    //     // $ticketId = (int) ($params['id']);
    //     $statusKey = (int) ($params['status']);
    //     $allTickets = $this->ticketService->getAll(['status' => $statusKey]);
    //     $ticketTypeIcons = $this->ticketService->getTypeIcons();
    //     $priorities = $this->ticketService->getPriorityLabels();
    //     $efforts = $this->ticketService->getEffortLabels();
    //     $milestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
    //     $users = $this->projectService->getUsersAssignedToProject(session('currentProject'));
    //     $onTheClock = $this->timesheetService->isClocked(session('userdata.id'));

    //     $this->tpl->assign('onTheClock', $onTheClock);
    //     $this->tpl->assign("efforts", $efforts);
    //     $this->tpl->assign("milestones", $milestones);
    //     $this->tpl->assign("users", $users);
    //     $this->tpl->assign("allTickets", $allTickets);
    //     $this->tpl->assign("ticketTypeIcons", $ticketTypeIcons);
    //     $this->tpl->assign("priorities", $priorities);
    //     $this->tpl->assign("statusKey", $statusKey);
    // }

    public function post($params): Response
    {
        if (! isset($_GET['id'])) {
            return $this->tpl->display('errors.error400', responseCode: 400);
        }

        // dd($params);

        $tab = '';
        $id = (int) ($_GET['id']);
        $ticket = $this->ticketService->getTicket($id);

        if ($ticket === false) {
            return $this->tpl->display('errors.error500', responseCode: 500);
        }

        if (!empty($params['tags']) && is_array($params['tags'])) {
            $params['tags'] = implode(',', $params['tags']);
        }

        //Log time
        if (isset($params['saveTimes']) === true) {
            $result = $this->timesheetService->logTime($id, $params);

            if ($result === true) {
                $this->tpl->setNotification($this->language->__('notifications.time_logged_success'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__($result['msg']), 'error');
            }
        }

        //Save Ticket
        $params['projectId'] = $ticket->projectId;
        $params['id'] = $id;

        //Prepare values, time comes in as 24hours from time input. Service expects time to be in local user format
        $params['timeToFinish'] = format(value: $params['timeToFinish'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $params['timeFrom'] = format(value: $params['timeFrom'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $params['timeTo'] = format(value: $params['timeTo'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();

        $result = $this->ticketService->updateTicket($params);

        if ($result === true) {
            $this->tpl->setNotification($this->language->__('notifications.ticket_saved'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__($result['msg']), 'error');
        }

        if (isset($params['saveAndCloseTicket']) === true && $params['saveAndCloseTicket'] == 1) {
            return response()->json(['success' => $result]);
        }


        return response()->json(['success' => $result]);
    }
}
