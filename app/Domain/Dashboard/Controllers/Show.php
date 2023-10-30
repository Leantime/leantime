<?php

namespace Leantime\Domain\Dashboard\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;

    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Reactions\Models\Reactions;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Reactions\Services\Reactions as ReactionService;
    use Leantime\Domain\Reports\Services\Reports as ReportService;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;

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
            ReactionService $reactionsService
        ): void {
            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->userService = $userService;
            $this->timesheetService = $timesheetService;
            $this->commentService = $commentService;
            $this->reactionsService = $reactionsService;

            $_SESSION['lastPage'] = BASE_URL . "/dashboard/show";


        }

        /**
         * @return void
         * @throws BindingResolutionException
         */
        public function get(): void
        {

            if (!isset($_SESSION['currentProject']) || $_SESSION['currentProject'] == '') {
                FrontcontrollerCore::redirect(BASE_URL . "/dashboard/home");
            }

            $project = $this->projectService->getProject($_SESSION['currentProject']);

            if (isset($project['id']) === false) {
                FrontcontrollerCore::redirect(BASE_URL . "/dashboard/home");
            }

            $projectRedirectFilter = static::dispatch_filter("dashboardRedirect", "/dashboard/show", array("type" => $project["type"]));
            if ($projectRedirectFilter != "/dashboard/show") {
                FrontcontrollerCore::redirect(BASE_URL . $projectRedirectFilter);
            }

            [$progressSteps, $percentDone] = $this->projectService->getProjectSetupChecklist($_SESSION['currentProject']);
            $this->tpl->assign("progressSteps", $progressSteps);
            $this->tpl->assign("percentDone", $percentDone);

            $project['assignedUsers'] = $this->projectService->getProjectUserRelation($_SESSION['currentProject']);
            $this->tpl->assign('project', $project);

            $userReaction = $this->reactionsService->getUserReactions($_SESSION['userdata']['id'], 'project', $_SESSION['currentProject'], Reactions::$favorite);
            if ($userReaction && is_array($userReaction) && count($userReaction) > 0) {
                $this->tpl->assign("isFavorite", true);
            } else {
                $this->tpl->assign("isFavorite", false);
            }

            $this->tpl->assign('allUsers', $this->userService->getAll());

            //Project Progress
            $progress = $this->projectService->getProjectProgress($_SESSION['currentProject']);
            $this->tpl->assign('projectProgress', $progress);
            $this->tpl->assign("currentProjectName", $this->projectService->getProjectName($_SESSION['currentProject']));

            //Milestones

            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $_SESSION["currentProject"]]);
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
            }, $comments->getComments('project', $_SESSION['currentProject'], 0));


            $url = parse_url(CURRENT_URL);
            $this->tpl->assign('delUrlBase', $url['scheme'] . '://' . $url['host'] . $url['path'] . '?delComment='); // for delete comment

            $this->tpl->assign('comments', $comment);
            $this->tpl->assign('numComments', $comments->countComments('project', $_SESSION['currentProject']));

            // TICKETS
            $this->tpl->assign('tickets', $this->ticketService->getLastTickets($_SESSION['currentProject']));
            $this->tpl->assign("onTheClock", $this->timesheetService->isClocked($_SESSION["userdata"]["id"]));
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());
            $this->tpl->assign("types", $this->ticketService->getTicketTypes());
            $this->tpl->assign("statusLabels", $this->ticketService->getStatusLabels());

            $this->tpl->display('dashboard.show');
        }


        /**
         * @param $params
         * @return void
         * @throws BindingResolutionException
         */
        public function post($params): void
        {

            if (AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor, Roles::$commenter])) {
                if (isset($params['quickadd'])) {
                    $result = $this->ticketService->quickAddTicket($params);

                    if (isset($result["status"])) {
                        $this->tpl->setNotification($result["message"], $result["status"]);
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success", "quickticket_created");
                    }

                    $this->tpl->redirect(BASE_URL . "/dashboard/show");
                }
            }

            // Manage Post comment
            $comments = app()->make(CommentRepository::class);
            if (isset($_POST['comment']) === true) {
                $project = $this->projectService->getProject($_SESSION['currentProject']);

                if ($this->commentService->addComment($_POST, "project", $_SESSION['currentProject'], $project)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success", "dashboardcomment_created");
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                }
            }

            $this->tpl->redirect(BASE_URL . "/dashboard/show");
        }
    }
}
