<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Carbon\Carbon;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Support\FromFormat;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Files\Services\Files as FileService;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class ShowTicket extends Controller
    {
        private ProjectService $projectService;
        private TicketService $ticketService;
        private SprintService $sprintService;
        private FileService $fileService;
        private CommentService $commentService;
        private TimesheetService $timesheetService;
        private UserService $userService;

        /**
         * @param ProjectService   $projectService
         * @param TicketService    $ticketService
         * @param SprintService    $sprintService
         * @param FileService      $fileService
         * @param CommentService   $commentService
         * @param TimesheetService $timesheetService
         * @param UserService      $userService
         * @return void
         */
        public function init(
            ProjectService $projectService,
            TicketService $ticketService,
            SprintService $sprintService,
            FileService $fileService,
            CommentService $commentService,
            TimesheetService $timesheetService,
            UserService $userService
        ): void {
            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->sprintService = $sprintService;
            $this->fileService = $fileService;
            $this->commentService = $commentService;
            $this->timesheetService = $timesheetService;
            $this->userService = $userService;

            if (session()->exists("lastPage") === false) {
                session(["lastPage" => BASE_URL . "/tickets/showKanban"]);
            }
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function get($params): Response
        {
            if (! isset($params['id'])) {
                return $this->tpl->displayPartial('errors.error400', responseCode: 400);
            }

            $id = (int)($params['id']);
            $ticket = $this->ticketService->getTicket($id);

            if ($ticket === false) {
                return $this->tpl->display('errors.error500', responseCode: 500);
            }

            //Ensure this ticket belongs to the current project
            if (session("currentProject") != $ticket->projectId) {
                $this->projectService->changeCurrentSessionProject($ticket->projectId);

                return Frontcontroller::redirect(BASE_URL . "/tickets/showTicket/" . $id);
            }

            //Delete file
            if (isset($params['delFile']) === true) {
                if ($result = $this->fileService->deleteFile($params['delFile'])) {
                    $this->tpl->setNotification($this->language->__("notifications.file_deleted"), "success");

                    return Frontcontroller::redirect(BASE_URL . "/tickets/showTicket/" . $id . "#files");;
                }

                $this->tpl->setNotification($result["msg"], "error");
            }

            //Delete comment
            if (isset($params['delComment']) === true) {
                $commentId = (int)($params['delComment']);

                if ($this->commentService->deleteComment($commentId)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");
                    $response = Frontcontroller::redirect(BASE_URL . "/tickets/showTicket/" . $id);
                    $response->headers->set('HX-Trigger', 'ticketUpdate');
                    return $response;
                }

                $this->tpl->setNotification($this->language->__("notifications.comment_deleted_error"), "error");
            }
            //Delete Subtask
            if (isset($params['delSubtask']) === true) {

            }

            $this->tpl->assign('ticket', $ticket);
            $this->tpl->assign('ticketParents', $this->ticketService->getAllPossibleParents($ticket));
            $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
            $this->tpl->assign('ticketTypes', $this->ticketService->getTicketTypes());
            $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

             $allProjectMilestones = $this->ticketService->getAllMilestones([
            "sprint" => '',
             "type" => "milestone",
             "currentProject" => session("currentProject"),
             ]);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign('sprints', $this->sprintService->getAllSprints(session("currentProject")));

            $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
            $this->tpl->assign('ticketHours', $this->timesheetService->getLoggedHoursForTicketByDate($id));
            $this->tpl->assign('userHours', $this->timesheetService->getUsersTicketHours($id, session("userdata.id")));

            $this->tpl->assign('timesheetsAllHours', $this->timesheetService->getSumLoggedHoursForTicket($id));
            $this->tpl->assign('remainingHours', $this->timesheetService->getRemainingHours($ticket));

            $this->tpl->assign('userInfo', $this->userService->getUser(session("userdata.id")));
            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($ticket->projectId));

            $projectData = $this->projectService->getProject($ticket->projectId);
            $this->tpl->assign('projectData', $projectData);

            $comments = $this->commentService->getComments('ticket', $id);

            $this->tpl->assign('numComments', count($comments));
            $this->tpl->assign('comments', $comments);

            $files = $this->fileService->getFilesByModule('ticket', $id);
            $this->tpl->assign('numFiles', count($files));
            $this->tpl->assign('files', $files);

            $this->tpl->assign('onTheClock', $this->timesheetService->isClocked(session("userdata.id")));

            $this->tpl->assign("timesheetValues", array(
                "kind" => "",
                "date" => Carbon::now(session("usersettings.timezone"))->setTimezone('UTC'),
                "hours" => "",
                "description" => "",
            ));

            //TODO: Refactor thumbnail generation in file manager
            $this->tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));

            $allAssignedprojects = $this->projectService->getProjectsUserHasAccessTo(session("userdata.id"), 'open');
            $this->tpl->assign('allAssignedprojects', $allAssignedprojects);

            $response = $this->tpl->displayPartial('tickets.showTicketModal');
            $response->headers->set('HX-Trigger', 'ticketUpdate');

            return $response;
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {
            if (! isset($_GET['id'])) {
                return $this->tpl->display('errors.error400', responseCode: 400);
            }

            $tab = "";
            $id = (int)($_GET['id']);
            $ticket = $this->ticketService->getTicket($id);

            if ($ticket === false) {
                return $this->tpl->display('errors.error500', responseCode: 500);
            }

            //Upload File
            if (isset($params['upload'])) {
                if ($this->fileService->uploadFile($_FILES, "ticket", $id, $ticket)) {
                    $this->tpl->setNotification($this->language->__("notifications.file_upload_success"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.file_upload_error"), "error");
                }

                $tab = "#files";
            }

            // Add or edit a comment
            if (isset($params['comment']) === true && isset($params['text']) && $params['text'] != '' && isset($params['edit-comment-helper']) && $params['edit-comment-helper'] !== "") {
                if ($this->commentService->editComment($_POST, (int)$params['edit-comment-helper'])) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_edited_success"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_edit_error"), "error");
                }
                $tab = "#comment";
            } else if (isset($params['comment']) === true && isset($params['text']) && $params['text'] != '') {
                if ($this->commentService->addComment($_POST, "ticket", $id, $ticket)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                }

                $tab = "#comment";
            }

            //Log time
            if (isset($params['saveTimes']) === true) {
                $result = $this->timesheetService->logTime($id, $params);

                if ($result === true) {
                    $this->tpl->setNotification($this->language->__("notifications.time_logged_success"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__($result['msg']), "error");
                }
            }


            //Save Ticket
            if (isset($params["saveTicket"]) === true || isset($params["saveAndCloseTicket"]) === true) {
                $params["projectId"] = $ticket->projectId;
                $params['id'] = $id;

                //Prepare values, time comes in as 24hours from time input. Service expects time to be in local user format
                $params['timeToFinish'] = format(value: $params['timeToFinish'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
                $params['timeFrom'] = format(value: $params['timeFrom'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
                $params['timeTo'] = format(value: $params['timeTo'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();

                $result = $this->ticketService->updateTicket($params);

                if ($result === true) {
                    $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__($result["msg"]), "error");
                }

                if (isset($params["saveAndCloseTicket"]) === true && $params["saveAndCloseTicket"] == 1) {
                    $response = Frontcontroller::redirect(BASE_URL . "/tickets/showTicket/" . $id . "?closeModal=1");
                    $response->headers->set('HX-Trigger', 'ticketUpdate');
                    return $response;
                }
            }

            $response = Frontcontroller::redirect(BASE_URL . "/tickets/showTicket/" . $id . "" . $tab);
            $response->headers->set('HX-Trigger', 'ticketUpdate');
            return $response;
        }
    }
}
