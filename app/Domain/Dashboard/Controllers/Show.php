<?php

namespace Leantime\Domain\Dashboard\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Reactions\Models\Reactions;
    use Leantime\Domain\Reactions\Services\Reactions as ReactionService;
    use Leantime\Domain\Setting\Repositories\Setting;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Show extends Controller
    {
        private ProjectService $projectService;
        private TicketService $ticketService;
        private UserService $userService;
        private TimesheetService $timesheetService;
        private CommentService $commentService;
        private ReactionService $reactionsService;
        private Setting $settingRepo;

        /**
         * @param ProjectService   $projectService
         * @param TicketService    $ticketService
         * @param UserService      $userService
         * @param TimesheetService $timesheetService
         * @param CommentService   $commentService
         * @param ReactionService  $reactionsService
         * @return void
         * @throws BindingResolutionException
         * @throws BindingResolutionException
         */
        public function init(
            ProjectService $projectService,
            TicketService $ticketService,
            UserService $userService,
            TimesheetService $timesheetService,
            CommentService $commentService,
            ReactionService $reactionsService,
            Setting $settingRepo
        ): void {
            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->userService = $userService;
            $this->timesheetService = $timesheetService;
            $this->commentService = $commentService;
            $this->reactionsService = $reactionsService;
            $this->settingRepo = $settingRepo;

            session(["lastPage" => BASE_URL . "/dashboard/show"]);
        }

        /**
         * @return Response
         * @throws BindingResolutionException
         */
        public function get(): Response
        {
            $currentProjectId = $this->projectService->getCurrentProjectId();
            if (0 === $currentProjectId) {
                return FrontcontrollerCore::redirect(BASE_URL . "/dashboard/home");
            }

            $project = $this->projectService->getProject($currentProjectId);
            if (isset($project['id']) === false) {
                return FrontcontrollerCore::redirect(BASE_URL . "/dashboard/home");
            }

            $projectRedirectFilter = self::dispatch_filter("dashboardRedirect", "/dashboard/show", array("type" => $project["type"]));
            if ($projectRedirectFilter != "/dashboard/show") {
                return FrontcontrollerCore::redirect(BASE_URL . $projectRedirectFilter);
            }

            [$progressSteps, $percentDone] = $this->projectService->getProjectSetupChecklist($currentProjectId);
            $this->tpl->assign("progressSteps", $progressSteps);
            $this->tpl->assign("percentDone", $percentDone);

            $project['assignedUsers'] = $this->projectService->getProjectUserRelation($currentProjectId);
            $this->tpl->assign('project', $project);

            $userReaction = $this->reactionsService->getUserReactions(session("userdata.id"), 'project', $currentProjectId, Reactions::$favorite);
            if ($userReaction && is_array($userReaction) && count($userReaction) > 0) {
                $this->tpl->assign("isFavorite", true);
            } else {
                $this->tpl->assign("isFavorite", false);
            }

            $this->tpl->assign('allUsers', $this->userService->getAll());

            //Project Progress
            $progress = $this->projectService->getProjectProgress($currentProjectId);
            $this->tpl->assign('projectProgress', $progress);
            $this->tpl->assign("currentProjectName", $this->projectService->getProjectName($currentProjectId));

            //Milestones

            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => session("currentProject")]);
            $this->tpl->assign('milestones', $allProjectMilestones);

            $comments = app()->make(CommentRepository::class);

            //Delete comment
            if (isset($_GET['delComment']) === true) {
                $commentId = (int)($_GET['delComment']);

                $comments->deleteComment($commentId);

                $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success", "projectcomment_deleted");
            }

            // add replies to comments
            $comment = array_map(function ($comment) use ($comments) {
                $comment['replies'] = $comments->getReplies($comment['id']);
                return $comment;
            }, $comments->getComments('project', $currentProjectId, 0));


            $url = parse_url(CURRENT_URL);
            $this->tpl->assign('delUrlBase', $url['scheme'] . '://' . $url['host'] . $url['path'] . '?delComment='); // for delete comment

            $this->tpl->assign('comments', $comment);
            $this->tpl->assign('numComments', $comments->countComments('project', $currentProjectId));

            $completedOnboarding = $this->settingRepo->getSetting("companysettings.completedOnboarding");
            $this->tpl->assign("completedOnboarding", $completedOnboarding);

            // TICKETS
            $this->tpl->assign('tickets', $this->ticketService->getLastTickets($currentProjectId));
            $this->tpl->assign("onTheClock", $this->timesheetService->isClocked(session("userdata.id")));
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());
            $this->tpl->assign("types", $this->ticketService->getTicketTypes());
            $this->tpl->assign("statusLabels", $this->ticketService->getStatusLabels());

            return $this->tpl->display('dashboard.show');
        }


        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {

            if (AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor, Roles::$commenter])) {
                if (isset($params['quickadd'])) {
                    $result = $this->ticketService->quickAddTicket($params);

                    if (isset($result["status"])) {
                        $this->tpl->setNotification($result["message"], $result["status"]);
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success", "quickticket_created");
                    }

                    return Frontcontroller::redirect(BASE_URL . "/dashboard/show");
                }
            }

            // Manage Post comment
            $comments = app()->make(CommentRepository::class);
            if (isset($_POST['comment']) === true) {
                $currentProjectId = $this->projectService->getCurrentProjectId();
                $project = $this->projectService->getProject($currentProjectId);

                if ($project && $this->commentService->addComment($_POST, "project", $currentProjectId, $project)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success", "dashboardcomment_created");
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                }
            }

            return Frontcontroller::redirect(BASE_URL . "/dashboard/show");
        }
    }
}
