<?php

namespace Leantime\Domain\Dashboard\Controllers;

use Carbon\CarbonImmutable;
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
 * TL / CM (Team Lead + Manager) home dashboard.
 *
 * Lists every project the user is assigned to as a one-line summary with
 * an expandable "More details" panel. Clicking the project row enters it.
 * Aggregated KPIs (overdue, blocked, open client requests) shown on top.
 * CM-only buttons (New Project, Manage Clients, All Projects) are gated.
 */
class TlcmHome extends Controller
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
        if (! AuthService::userHasRole([Roles::$teamlead, Roles::$manager], true)) {
            Frontcontroller::redirect(BASE_URL . '/dashboard/home');
        }

        $this->projectService = $projectService;
        $this->ticketRepository = $ticketRepository;
        $this->clientPortalService = $clientPortalService;

        session(['lastPage' => BASE_URL . '/dashboard/tlcmHome']);
    }

    /**
     * @throws BindingResolutionException
     */
    public function get(): Response
    {
        $userId = (int) session('userdata.id');
        $role = session('userdata.role');
        $isCM = $role === Roles::$manager;
        $isAdmin = AuthService::userIsAtLeast(Roles::$admin, true);

        // All active projects assigned to the user
        $myProjects = $this->projectService->getProjectsAssignedToUser($userId) ?: [];
        $myProjects = array_filter($myProjects, fn($p) => ($p['state'] ?? 0) != -1);

        $cards = [];
        $totalOverdue = 0;
        $totalBlocked = 0;
        $totalOpenTasks = 0;
        $totalOpenReqs = 0;
        $totalPendingReviews = 0;
        $now = CarbonImmutable::now();

        foreach ($myProjects as $project) {
            $pid = (int) $project['id'];

            $progress = $this->projectService->getProjectProgress($pid);
            $team = $this->projectService->getUsersAssignedToProject($pid) ?: [];

            $tickets = $this->ticketRepository->getAllBySearchCriteria(
                ['currentProject' => $pid, 'type' => 'task'],
                'standard',
                null,
                false
            );

            $openCount = 0;
            $overdueCount = 0;
            $blockedCount = 0;
            $recentActivity = [];

            if (is_array($tickets)) {
                foreach ($tickets as $t) {
                    $isObj = is_object($t);
                    $status = (int) ($isObj ? ($t->status ?? 0) : ($t['status'] ?? 0));
                    $dateToFinish = $isObj ? ($t->dateToFinish ?? '') : ($t['dateToFinish'] ?? '');
                    $modified = $isObj ? ($t->modified ?? '') : ($t['modified'] ?? '');
                    $date = $isObj ? ($t->date ?? '') : ($t['date'] ?? '');
                    $headline = $isObj ? ($t->headline ?? '') : ($t['headline'] ?? '');
                    $tid = (int) ($isObj ? ($t->id ?? 0) : ($t['id'] ?? 0));
                    $editorFirst = $isObj ? ($t->editorFirstname ?? '') : ($t['editorFirstname'] ?? '');
                    $editorLast = $isObj ? ($t->editorLastname ?? '') : ($t['editorLastname'] ?? '');

                    if ($status >= 0 && $status < 3) {
                        $openCount++;
                    }

                    if (! empty($dateToFinish) && $dateToFinish !== '0000-00-00 00:00:00' && $status < 3) {
                        try {
                            if (CarbonImmutable::parse($dateToFinish)->lt($now)) {
                                $overdueCount++;
                            }
                        } catch (\Exception) {
                        }
                    }

                    if ($status === 1) {
                        $blockedCount++;
                    }

                    $activityDate = $modified ?: $date;
                    if (! empty($activityDate)) {
                        $recentActivity[] = [
                            'id' => $tid,
                            'headline' => $headline,
                            'modified' => $activityDate,
                            'editorFirstname' => $editorFirst,
                            'editorLastname' => $editorLast,
                        ];
                    }
                }
            }

            usort($recentActivity, fn($a, $b) => strcmp($b['modified'], $a['modified']));
            $recentActivity = array_slice($recentActivity, 0, 4);

            $reqs = $this->clientPortalService->getRequestsForProject($pid) ?: [];
            $openRequests = array_values(array_filter($reqs, fn($r) => ($r['status'] ?? '') === 'open'));

            // Milestones waiting for senior review (status = 5)
            $pendingMilestones = $this->ticketRepository->getMilestonesReadyForReview($pid);

            // Health rollup
            $health = 'on_track';
            if ($overdueCount > 0 || $blockedCount > 0) {
                $health = 'at_risk';
            }
            if (count($pendingMilestones) > 0) {
                $health = 'at_risk';
            }

            $totalOpenTasks += $openCount;
            $totalOverdue += $overdueCount;
            $totalBlocked += $blockedCount;
            $totalOpenReqs += count($openRequests);
            $totalPendingReviews += count($pendingMilestones);

            $cards[] = [
                'project' => $project,
                'progress' => $progress,
                'team' => $team,
                'openCount' => $openCount,
                'overdueCount' => $overdueCount,
                'blockedCount' => $blockedCount,
                'recentActivity' => $recentActivity,
                'openRequests' => $openRequests,
                'pendingMilestones' => $pendingMilestones,
                'health' => $health,
            ];
        }

        // Sort: at_risk first, then on_track; within each by name
        usort($cards, function ($a, $b) {
            $order = ['at_risk' => 0, 'on_track' => 1];
            $aO = $order[$a['health']] ?? 2;
            $bO = $order[$b['health']] ?? 2;
            if ($aO !== $bO) {
                return $aO - $bO;
            }

            return strcmp($a['project']['name'] ?? '', $b['project']['name'] ?? '');
        });

        $this->tpl->assign('isCM', $isCM);
        $this->tpl->assign('isAdmin', $isAdmin);
        $this->tpl->assign('cards', $cards);
        $this->tpl->assign('totalActiveProjects', count($cards));
        $this->tpl->assign('totalOpenTasks', $totalOpenTasks);
        $this->tpl->assign('totalOverdue', $totalOverdue);
        $this->tpl->assign('totalBlocked', $totalBlocked);
        $this->tpl->assign('totalOpenReqs', $totalOpenReqs);
        $this->tpl->assign('totalPendingReviews', $totalPendingReviews);

        return $this->tpl->display('dashboard.tlcmHome');
    }
}
