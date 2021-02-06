<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\services;

    class showTicket
    {

        private $projectService;
        private $ticketService;
        private $tpl;
        private $sprintService;
        private $fileService;
        private $commentService;
        private $timesheetService;
        private $userService;
        private $language;

        public function __construct()
        {
            $this->tpl = new core\template();

            $this->language = new core\language();

            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->sprintService = new services\sprints();
            $this->fileService = new services\files();
            $this->commentService = new services\comments();
            $this->timesheetService = new services\timesheets();
            $this->userService = new services\users();

            if(isset($_SESSION['lastPage']) === false){
                $_SESSION['lastPage'] = BASE_URL."/tickets/showKanban";
            }
        }

        public function get($params)
        {

            if (isset($params['id']) === true) {

                $id = (int)($params['id']);
                $ticket = $this->ticketService->getTicket($id);

                if($ticket === false) {
                    $this->tpl->display('general.error');
                    return;
                }

                //Ensure this ticket belongs to the current project
                if($_SESSION["currentProject"] != $ticket->projectId) {
                    $this->projectService->changeCurrentSessionProject($ticket->projectId);
                    $this->tpl->redirect(BASE_URL."/tickets/showTicket/".$id);
                }
                //Delete file
                if (isset($params['delFile']) === true) {

                    $result = $this->fileService->deleteFile($params['delFile']);

                    if($result === true) {
                        $this->tpl->setNotification($this->language->__("notifications.file_deleted"), "success");
                        $this->tpl->redirect(BASE_URL."/tickets/showTicket/".$id."#files");
                    }else {
                        $this->tpl->setNotification($result["msg"], "error");
                    }
                }

                //Delete comment
                if (isset($params['delComment']) === true) {

                    $commentId = (int)($params['delComment']);

                    if($this->commentService->deleteComment($commentId)){
                        $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");
                        $this->tpl->redirect(BASE_URL."/tickets/showTicket/".$id);
                    }else{
                        $this->tpl->setNotification($this->language->__("notifications.comment_deleted_error"), "error");
                    }
                }

                $this->tpl->assign('ticket', $ticket);
                $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
                $this->tpl->assign('ticketTypes', $this->ticketService->getTicketTypes());
                $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
                $this->tpl->assign('milestones', $this->ticketService->getAllMilestones($_SESSION["currentProject"]));
                $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));

                $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
                $this->tpl->assign('ticketHours', $this->timesheetService->getLoggedHoursForTicketByDate($id));
                $this->tpl->assign('userHours', $this->timesheetService->getUsersTicketHours($id, $_SESSION['userdata']['id']));

                $this->tpl->assign('timesheetsAllHours', $this->timesheetService->getSumLoggedHoursForTicket($id));
                $this->tpl->assign('remainingHours', $this->timesheetService->getRemainingHours($ticket));

                $this->tpl->assign('userInfo', $this->userService->getUser($_SESSION['userdata']['id']));
                $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($ticket->projectId));

                $projectData = $this->projectService->getProject($ticket->projectId);
				$this->tpl->assign('projectData', $projectData);

				$comments = $this->commentService->getComments('ticket', $id, $_SESSION["projectsettings"]['commentOrder']);

                $this->tpl->assign('numComments', count($comments));
                $this->tpl->assign('comments', $comments);

                $subTasks = $this->ticketService->getAllSubtasks($id);
                $this->tpl->assign('numSubTasks', count($subTasks));
                $this->tpl->assign('allSubTasks', $subTasks);

                $files = $this->fileService->getFilesByModule('ticket', $id);
                $this->tpl->assign('numFiles', count($files));
                $this->tpl->assign('files', $files);

                $this->tpl->assign("timesheetValues", array("kind"=>"", "date"=>date($this->language->__("language.dateformat")), "hours"=>"", "description"=>""));

                //TODO: Refactor thumbnail generation in file manager
                $this->tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));

                $this->tpl->display('tickets.showTicket');

            } else {

                $this->tpl->display('general.error');

            }

        }

        public function post($params)
        {

            if (isset($_GET['id']) === true) {

                $id = (int)($_GET['id']);
                $ticket = $this->ticketService->getTicket($id);

                if($ticket === false) {
                    $this->tpl->display('general.error');
                    return;
                }

                //Upload File
                if (isset($params['upload'])) {

                    if ($this->fileService->uploadFile($_FILES, "ticket", $id, $ticket)) {
                        $this->tpl->setNotification($this->language->__("notifications.file_upload_success"), "success");
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.file_upload_error"), "error");
                    }
                }

                //Add a comment
                if (isset($params['comment']) === true) {

                    if($this->commentService->addComment($_POST, "ticket", $id, $ticket)) {
                        $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                    }else {
                        $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                    }
                }

                //Log time
                if (isset($params['saveTimes']) === true) {

                    $result = $this->timesheetService->logTime($id, $params);

                    if($result === true){
                        $this->tpl->setNotification($this->language->__("notifications.time_logged_success"), "success");
                    }else{
                        $this->tpl->setNotification($this->language->__($result['msg']), "error");
                    }
                }

                //Save Substask
                if (isset($params['subtaskSave']) === true) {

                    if($this->ticketService->upsertSubtask($params, $ticket)) {
                        $this->tpl->setNotification($this->language->__("notifications.subtask_saved"), "success");
                    }else {
                        $this->tpl->setNotification($this->language->__("notifications.subtask_save_error"), "error");
                    }

                }

                //Delete Subtask
                if (isset($params['subtaskDelete']) === true) {

                    $subtaskId = $params['subtaskId'];
                    if($this->ticketService->deleteTicket($subtaskId)) {
                        $this->tpl->setNotification($this->language->__("notifications.subtask_deleted"), "success");
                    }else {
                        $this->tpl->setNotification($this->language->__("notifications.subtask_delete_error"), "error");
                    }
                }

                //Save Ticket
                if (isset($params["saveTicket"]) === true || isset($params["saveAndCloseTicket"]) === true) {

                    $result = $this->ticketService->updateTicket($id, $params);

                    if($result === true) {
                        $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");
                    }else {
                        $this->tpl->setNotification($this->language->__($result["msg"]), "error");
                    }

                    if(isset($params["saveAndCloseTicket"]) === true) {
                        $this->tpl->redirect($_SESSION['lastPage']);
                    }
                }

                $this->tpl->redirect(BASE_URL."/tickets/showTicket/".$id);

            } else {

                $this->tpl->display('general.error');

            }

        }

    }
}