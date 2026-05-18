<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets;
use Symfony\Component\HttpFoundation\Response;

class Milestones extends HtmxController
{
    protected static string $view = 'tickets::partials.milestoneCard';

    private Tickets $ticketService;

    private ProjectService $projectService;

    /**
     * Controller constructor
     */
    public function init(Tickets $ticketService, ProjectService $projectService): void
    {
        $this->ticketService = $ticketService;
        $this->projectService = $projectService;
    }

    public function progress()
    {

        $getParams = $_GET;

        $milestone = $this->ticketService->getTicket($getParams['milestoneId']);
        $percentDone = $this->ticketService->getMilestoneProgress($getParams['milestoneId']);
        $percentDone = $this->overrideProgressIfDone($milestone, $percentDone);

        $this->tpl->assign('progressColor', $getParams['progressColor'] ?? 'default');
        $this->tpl->assign('noText', $getParams['noText'] ?? false);
        $this->tpl->assign('milestone', $milestone);
        $this->tpl->assign('percentDone', $percentDone);

        return 'progress';
    }

    public function showCard()
    {

        $getParams = $_GET;

        $milestone = $this->ticketService->getTicket($getParams['milestoneId']);
        $percentDone = $this->ticketService->getMilestoneProgress($getParams['milestoneId']);
        $percentDone = $this->overrideProgressIfDone($milestone, $percentDone);

        $this->tpl->assign('percentDone', $percentDone);
        $this->tpl->assign('milestone', $milestone);
    }

    /**
     * If the milestone itself is in a DONE status, always report 100% regardless of task count.
     */
    private function overrideProgressIfDone(mixed $milestone, float $percentDone): float
    {
        if (! $milestone) {
            return $percentDone;
        }

        $statusLabels = $this->ticketService->getStatusLabels($milestone->projectId ?? null);
        $milestoneStatus = $milestone->status ?? null;

        if (
            $milestoneStatus !== null
            && isset($statusLabels[$milestoneStatus])
            && ($statusLabels[$milestoneStatus]['statusType'] ?? '') === 'DONE'
        ) {
            return 100.0;
        }

        return $percentDone;
    }

    /**
     * Returns JSON array of {id, name} for users assigned to the given project.
     * Called via fetch when the project dropdown changes in the new-ticket form.
     * GET /hx/tickets/milestones/usersByProject?projectId=<id>
     */
    public function usersByProject(): Response
    {
        $projectId = (int) ($_GET['projectId'] ?? 0);

        if ($projectId <= 0 || ! $this->projectService->isUserAssignedToProject((int) session('userdata.id'), $projectId)) {
            return new Response('[]', 200, ['Content-Type' => 'application/json']);
        }

        $users = [];
        $raw = $this->projectService->getUsersAssignedToProject($projectId);
        foreach ($raw as $u) {
            $users[] = [
                'id' => (int) $u['id'],
                'name' => trim(($u['firstname'] ?? '').' '.($u['lastname'] ?? '')),
            ];
        }

        return new Response(
            json_encode($users),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Returns <option> elements for milestones belonging to the given project.
     * Called via fetch when the project dropdown changes in the new-ticket form.
     * GET /hx/tickets/milestones/byProject?projectId=<id>
     */
    public function byProject(): Response
    {
        $projectId = (int) ($_GET['projectId'] ?? 0);

        if ($projectId <= 0 || ! $this->projectService->isUserAssignedToProject((int) session('userdata.id'), $projectId)) {
            $html = '<option value="">'.$this->language->__('label.not_assigned_to_milestone').'</option>';

            return new Response($html, 200, ['Content-Type' => 'text/html']);
        }

        $milestones = $this->ticketService->getAllMilestones([
            'sprint' => '',
            'type' => 'milestone',
            'currentProject' => $projectId,
        ]);

        $html = '<option value="">'.$this->language->__('label.not_assigned_to_milestone').'</option>';
        if (is_array($milestones)) {
            foreach ($milestones as $m) {
                $html .= '<option value="'.(int) $m->id.'">'.htmlspecialchars($m->headline, ENT_QUOTES).'</option>';
            }
        }

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
}
