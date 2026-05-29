<?php

namespace Leantime\Domain\Dashboard\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Dashboard\Services\Dashboard as DashboardService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Show extends Controller
{
    private ProjectService $projectService;

    private TicketService $ticketService;

    private UserService $userService;

    private TimesheetService $timesheetService;

    private DashboardService $dashboardService;

    private Setting $settingsSvc;

    /**
     * @throws BindingResolutionException
     */
    public function init(
        ProjectService $projectService,
        TicketService $ticketService,
        UserService $userService,
        TimesheetService $timesheetService,
        DashboardService $dashboardService,
        Setting $settingsSvc
    ): void {
        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->userService = $userService;
        $this->timesheetService = $timesheetService;
        $this->dashboardService = $dashboardService;
        $this->settingsSvc = $settingsSvc;

        session(['lastPage' => BASE_URL.'/dashboard/show']);
    }

    /**
     * @throws BindingResolutionException
     */
    public function get(): Response
    {
        $currentProjectId = $this->projectService->getCurrentProjectId();
        if ($currentProjectId === 0) {
            return FrontcontrollerCore::redirect(BASE_URL.'/dashboard/home');
        }

        $project = $this->projectService->getProject($currentProjectId);
        if (isset($project['id']) === false) {
            return FrontcontrollerCore::redirect(BASE_URL.'/dashboard/home');
        }

        $projectRedirectFilter = self::dispatch_filter('dashboardRedirect', '/dashboard/show', ['type' => $project['type']]);
        if ($projectRedirectFilter != '/dashboard/show') {
            return FrontcontrollerCore::redirect(BASE_URL.$projectRedirectFilter);
        }

        [$progressSteps, $percentDone] = $this->projectService->getProjectSetupChecklist($currentProjectId);
        $this->tpl->assign('progressSteps', $progressSteps);
        $this->tpl->assign('percentDone', $percentDone);

        $project['assignedUsers'] = $this->projectService->getUsersAssignedToProject($currentProjectId);
        $this->tpl->assign('project', $project);

        $this->tpl->assign('isFavorite', $this->dashboardService->userHasFavoritedProject(session('userdata.id'), $currentProjectId));

        $this->tpl->assign('allUsers', $this->userService->getAll());

        // Project Progress
        $progress = $this->projectService->getProjectProgress($currentProjectId);
        $this->tpl->assign('projectProgress', $progress);
        $this->tpl->assign('currentProjectName', $this->projectService->getProjectName($currentProjectId));

        // Milestones

        $allProjectMilestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
        $this->tpl->assign('milestones', $allProjectMilestones);

        // Delete comment (only confirm success when the auth-checked delete actually ran)
        if (isset($_GET['delComment']) === true) {
            if ($this->dashboardService->deleteProjectComment((int) $_GET['delComment'])) {
                $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success', 'projectcomment_deleted');
            }
        }

        $this->tpl->assign('delUrlBase', $this->dashboardService->buildDeleteCommentUrlBase());
        $this->tpl->assign('comments', $this->dashboardService->getProjectCommentsWithReplies($currentProjectId));
        $this->tpl->assign('numComments', $this->dashboardService->countProjectComments($currentProjectId));

        $completedOnboarding = $this->settingsSvc->onboardingHandler();
        if ($completedOnboarding instanceof RedirectResponse) {
            return $completedOnboarding;
        }

        $this->tpl->assign('completedOnboarding', $completedOnboarding);

        // TICKETS
        $this->tpl->assign('tickets', $this->ticketService->getLastTickets($currentProjectId));
        $this->tpl->assign('onTheClock', $this->timesheetService->isClocked(session('userdata.id')));
        $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
        $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());
        $this->tpl->assign('types', $this->ticketService->getTicketTypes());
        $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());

        return $this->tpl->display('dashboard.show');
    }

    /**
     * @throws BindingResolutionException
     */
    public function post($params): Response
    {

        if (AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor, Roles::$commenter])) {
            if (isset($params['quickadd'])) {
                $result = $this->ticketService->quickAddTicket($params);

                if (isset($result['status'])) {
                    $this->tpl->setNotification($result['message'], $result['status']);
                } else {
                    $this->tpl->setNotification($this->language->__('notifications.ticket_saved'), 'success', 'quickticket_created');
                }

                return Frontcontroller::redirect(BASE_URL.'/dashboard/show');
            }
        }

        // Manage Post comment
        if (isset($_POST['comment']) === true) {
            $currentProjectId = $this->projectService->getCurrentProjectId();
            $project = $this->projectService->getProject($currentProjectId);

            if ($project && $this->dashboardService->addProjectComment($_POST, $currentProjectId, $project)) {
                $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success', 'dashboardcomment_created');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.comment_create_error'), 'error');
            }
        }

        return Frontcontroller::redirect(BASE_URL.'/dashboard/show');
    }
}
