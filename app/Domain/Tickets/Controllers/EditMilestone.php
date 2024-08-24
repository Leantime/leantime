<?php

namespace Leantime\Domain\Tickets\Controllers {


    use DateInterval;
    use DateTime;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;

    /**
     *
     */
    class EditMilestone extends Controller
    {
        private TicketService $ticketService;
        private CommentService $commentsService;
        private ProjectService $projectService;
        private TicketRepository $ticketRepo;
        private ProjectRepository $projectRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            TicketService $ticketService,
            CommentService $commentsService,
            ProjectService $projectService,
            TicketRepository $ticketRepo,
            ProjectRepository $projectRepo
        ) {
            $this->ticketService = $ticketService;
            $this->commentsService = $commentsService;
            $this->projectService = $projectService;
            $this->ticketRepo = $ticketRepo;
            $this->projectRepo = $projectRepo;
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {
            if (isset($params['id'])) {
                //Delete comment
                if (isset($params['delComment']) === true) {
                    $commentId = (int)($params['delComment']);
                    $this->commentsService->deleteComment($commentId);

                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");
                }

                $milestone = $this->ticketRepo->getTicket($params['id']);
                $milestone = (object) $milestone;

                if (!isset($milestone->id)) {
                    $this->tpl->setNotification($this->language->__("notifications.could_not_find_milestone"), "error");
                    return Frontcontroller::redirect(BASE_URL . "/tickets/roadmap/");
                }

                //Ensure this ticket belongs to the current project
                if (session("currentProject") != $milestone->projectId) {
                    $this->projectService->changeCurrentSessionProject($milestone->projectId);
                    return Frontcontroller::redirect(BASE_URL . "/tickets/editMilestone/" . $milestone->id);
                }

                $comments = $this->commentsService->getComments('ticket', $params['id']);
            } else {
                $milestone = app()->make(TicketModel::class);
                $milestone->status = 3;

                $today = new DateTime();
                $milestone->editFrom = $today->format("Y-m-d");

                //Add 1 week
                $interval = new DateInterval('P1W');
                $next_week = $today->add($interval);

                $milestone->editTo = $next_week->format("Y-m-d");

                $comments = [];
            }

            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser(session("userdata.id"), 'open');
            $this->tpl->assign('allAssignedprojects', $allAssignedprojects);


            $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
            $this->tpl->assign('comments', $comments);

            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => session("currentProject")]);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign('users', $this->projectRepo->getUsersAssignedToProject(session("currentProject")));
            $this->tpl->assign('milestone', $milestone);
            return $this->tpl->displayPartial('tickets.milestoneDialog');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            //If ID is set its an update
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $params['id'] = (int)$_GET['id'];
                $milestone = $this->ticketRepo->getTicket($params['id']);

                if (isset($params['comment']) === true) {
                    $values = array(
                        'text' => $params['text'],
                        'date' => date("Y-m-d H:i:s"),
                        'userId' => (session("userdata.id")),
                        'moduleId' => $params['id'],
                        'father' => ($params['father']),
                    );


                    $messageId = $this->commentsService->addComment($values, 'ticket', $params['id'], $milestone);
                    $values['id'] = $messageId;
                    if ($messageId) {
                        $this->tpl->setNotification($this->language->__("notifications.comment_added_successfully"), "success");

                        $subject = $this->language->__("email_notifications.new_comment_milestone_subject");
                        $actual_link = BASE_URL . "/tickets/editMilestone/" . (int)$_GET['id'];
                        $message = sprintf($this->language->__("email_notifications.new_comment_milestone_message"), session("userdata.name"));


                        $notification = app()->make(NotificationModel::class);
                        $notification->url = array(
                            "url" => $actual_link,
                            "text" => $this->language->__("email_notifications.new_comment_milestone_cta"),
                        );
                        $notification->entity = $values;
                        $notification->module = "comments";
                        $notification->projectId = session("currentProject");
                        $notification->subject = $subject;
                        $notification->authorId = session("userdata.id");
                        $notification->message = $message;

                        $this->projectService->notifyProjectUsers($notification);
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.problem_saving_your_comment"), "error");
                    }

                    return Frontcontroller::redirect(BASE_URL . "/tickets/editMilestone/" . $params['id']);
                }

                if (isset($params['headline']) === true) {
                    if ($this->ticketService->quickUpdateMilestone($params)) {
                        $this->tpl->setNotification($this->language->__("notification.milestone_edited_successfully"), "success");

                        $subject = $this->language->__("email_notifications.milestone_update_subject");
                        $actual_link = BASE_URL . "/tickets/editMilestone/" . (int)$_GET['id'];
                        $message = sprintf($this->language->__("email_notifications.milestone_update_message"), session("userdata.name"));

                        $notification = app()->make(NotificationModel::class);
                        $notification->url = array(
                            "url" => $actual_link,
                            "text" => $this->language->__("email_notifications.milestone_update_cta"),
                        );
                        $notification->entity = $params;
                        $notification->module = "tickets";
                        $notification->projectId = session("currentProject");
                        $notification->subject = $subject;
                        $notification->authorId = session("userdata.id");
                        $notification->message = $message;

                        $this->projectService->notifyProjectUsers($notification);
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.saving_milestone_error"), "error");
                    }
                    return Frontcontroller::redirect(BASE_URL . "/tickets/editMilestone/" . $params['id']);
                }

                return Frontcontroller::redirect(BASE_URL . "/tickets/editMilestone/" . $params['id']);
            } else {
                $result = $this->ticketService->quickAddMilestone($params);

                if (is_numeric($result)) {
                    $params["id"] = $result;

                    $this->tpl->setNotification($this->language->__("notification.milestone_created_successfully"), "success");

                    $subject = $this->language->__("email_notifications.milestone_created_subject");
                    $actual_link = BASE_URL . "/tickets/editMilestone/" . $result;
                    $message = sprintf($this->language->__("email_notifications.milestone_created_message"), session("userdata.name"));

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__("email_notifications.milestone_created_cta"),
                    );
                    $notification->entity = $params;
                    $notification->module = "tickets";
                    $notification->projectId = session("currentProject");
                    $notification->subject = $subject;
                    $notification->authorId = session("userdata.id");
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    return Frontcontroller::redirect(BASE_URL . "/tickets/editMilestone/" . $result);
                } else {
                    $this->tpl->setNotification($this->language->__("notification.saving_milestone_error"), "error");
                    return Frontcontroller::redirect(BASE_URL . "/tickets/editMilestone/");
                }
            }

            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser(session("userdata.id"), 'open');
            $this->tpl->assign('allAssignedprojects', $allAssignedprojects);

            $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
            $this->tpl->assign('milestone', (object) $params);
            return $this->tpl->displayPartial('tickets.milestoneDialog');
        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {
        }
    }
}
