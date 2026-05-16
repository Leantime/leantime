<?php

namespace Leantime\Domain\Dashboard\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\ClientPortal\Services\ClientPortal as ClientPortalService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin home dashboard — project cards overview for admin/owner roles.
 */
class AdminHome extends Controller
{
    private ProjectService $projectService;

    private TicketRepository $ticketRepository;

    private ClientPortalService $clientPortalService;

    /**
     * @throws BindingResolutionException
     */
    public function init(
        ProjectService $projectService,
        TicketRepository $ticketRepository,
        ClientPortalService $clientPortalService,
    ): void {
        if (! AuthService::userHasRole([Roles::$owner, Roles::$admin])) {
            Frontcontroller::redirect(BASE_URL.'/dashboard/home');
        }

        $this->projectService = $projectService;
        $this->ticketRepository = $ticketRepository;
        $this->clientPortalService = $clientPortalService;

        session(['lastPage' => BASE_URL.'/dashboard/adminHome']);
    }

    /**
     * @throws BindingResolutionException
     */
    public function get(): Response
    {
        $allProjects = $this->projectService->getAllProjects();

        $activeProjects = array_filter($allProjects, fn ($p) => ($p['state'] ?? 0) != -1);

        $projectCards = [];
        $totalOverdue = 0;
        $totalBlocked = 0;

        foreach ($activeProjects as $project) {
            $projectId = (int) $project['id'];

            $progress = $this->projectService->getProjectProgress($projectId);
            $team = $this->projectService->getUsersAssignedToProject($projectId);

            // Ticket counts
            $allTickets = $this->ticketRepository->getAllBySearchCriteria(
                ['currentProject' => $projectId, 'type' => 'task'],
                'standard',
                null,
                false
            );

            $overdueCount = 0;
            $blockedCount = 0;
            $recentActivity = [];

            if (is_array($allTickets)) {
                $now = new \DateTime;
                foreach ($allTickets as $ticket) {
                    // Tickets model uses object properties
                    $dateToFinish = is_object($ticket) ? ($ticket->dateToFinish ?? '') : ($ticket['dateToFinish'] ?? '');
                    $status       = is_object($ticket) ? ($ticket->status ?? 0)        : ($ticket['status'] ?? 0);
                    $modified     = is_object($ticket) ? ($ticket->modified ?? '')      : ($ticket['modified'] ?? '');
                    $date         = is_object($ticket) ? ($ticket->date ?? '')          : ($ticket['date'] ?? '');
                    $headline     = is_object($ticket) ? ($ticket->headline ?? '')      : ($ticket['headline'] ?? '');
                    $editorFirst  = is_object($ticket) ? ($ticket->editorFirstname ?? '') : ($ticket['editorFirstname'] ?? '');
                    $editorLast   = is_object($ticket) ? ($ticket->editorLastname ?? '')  : ($ticket['editorLastname'] ?? '');

                    // Overdue: has a due date, not done, past due
                    if (! empty($dateToFinish) && $dateToFinish !== '0000-00-00 00:00:00' && (int) $status < 3) {
                        try {
                            if (new \DateTime($dateToFinish) < $now) {
                                $overdueCount++;
                            }
                        } catch (\Exception) {
                        }
                    }

                    // Blocked: status = 1
                    if ((int) $status === 1) {
                        $blockedCount++;
                    }

                    // Collect recent activity
                    $activityDate = $modified ?: $date;
                    if (! empty($activityDate)) {
                        $recentActivity[] = [
                            'headline'        => $headline,
                            'modified'        => $activityDate,
                            'editorFirstname' => $editorFirst,
                            'editorLastname'  => $editorLast,
                        ];
                    }
                }
            }

            // Sort recent activity by date desc, take top 3
            usort($recentActivity, fn ($a, $b) => strcmp($b['modified'], $a['modified']));
            $recentActivity = array_slice($recentActivity, 0, 3);

            // Upcoming milestones
            $milestones = $this->ticketRepository->getAllMilestones(
                ['currentProject' => $projectId, 'type' => 'milestone'],
                'standard'
            );

            $upcomingMilestones = [];
            if (is_array($milestones)) {
                $now = new \DateTime;
                foreach ($milestones as $ms) {
                    // getAllMilestones also returns Tickets model objects
                    $editTo   = is_object($ms) ? ($ms->editTo ?? '')   : ($ms['editTo'] ?? '');
                    $msStatus = is_object($ms) ? ($ms->status ?? 0)    : ($ms['status'] ?? 0);
                    $msTitle  = is_object($ms) ? ($ms->headline ?? '')  : ($ms['headline'] ?? '');

                    if (! empty($editTo) && $editTo !== '0000-00-00 00:00:00' && (int) $msStatus < 3) {
                        try {
                            $msDate = new \DateTime($editTo);
                            if ($msDate >= $now) {
                                $upcomingMilestones[] = [
                                    'headline' => $msTitle,
                                    'date'     => $editTo,
                                ];
                            }
                        } catch (\Exception) {
                        }
                    }
                }
                usort($upcomingMilestones, fn ($a, $b) => strcmp($a['date'], $b['date']));
                $upcomingMilestones = array_slice($upcomingMilestones, 0, 3);
            }

            // Health: at-risk if overdue > 0 or blocked > 0 or stale (no activity 7 days)
            $health = 'on_track';
            if ($overdueCount > 0 || $blockedCount > 0) {
                $health = 'at_risk';
            } elseif (! empty($project['modified'])) {
                try {
                    $lastMod = new \DateTime($project['modified']);
                    $diff = (new \DateTime)->diff($lastMod);
                    if ($diff->days > 7) {
                        $health = 'idle';
                    }
                } catch (\Exception) {
                }
            }

            $totalOverdue += $overdueCount;
            $totalBlocked += $blockedCount;

            $projectCards[] = [
                'project' => $project,
                'progress' => $progress,
                'team' => is_array($team) ? $team : [],
                'overdueCount' => $overdueCount,
                'blockedCount' => $blockedCount,
                'recentActivity' => $recentActivity,
                'upcomingMilestones' => $upcomingMilestones,
                'health' => $health,
            ];
        }

        // Sort: at_risk first, then idle, then on_track; within each group by project name
        usort($projectCards, function ($a, $b) {
            $order = ['at_risk' => 0, 'idle' => 1, 'on_track' => 2];
            $aO = $order[$a['health']] ?? 3;
            $bO = $order[$b['health']] ?? 3;
            if ($aO !== $bO) {
                return $aO - $bO;
            }

            return strcmp($a['project']['name'], $b['project']['name']);
        });

        // KPI: open client requests
        $openRequests = $this->clientPortalService->countOpenRequests();

        $this->tpl->assign('projectCards', $projectCards);
        $this->tpl->assign('totalActiveProjects', count($activeProjects));
        $this->tpl->assign('totalOverdue', $totalOverdue);
        $this->tpl->assign('totalBlocked', $totalBlocked);
        $this->tpl->assign('openClientRequests', $openRequests);

        return $this->tpl->display('dashboard.adminHome');
    }
}
