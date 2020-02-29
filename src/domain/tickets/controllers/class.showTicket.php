<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showTicket
    {

        public function __construct()
        {
            $this->projectService = new services\projects();

            if(!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = "/tickets/showKanban/";
            }

        }
        /**
         * run - display template and edit data
         *
         * @access public
         */

        public function run()
        {


            $tpl = new core\template();
            $projects = new repositories\projects();
            $ticketRepo = new repositories\tickets();
            $sprintService = new services\sprints();
            $ticketService = new services\tickets();


            $msgKey = '';

            $language = new core\language();

            $lang = $language->readIni();

            if (isset($_GET['id']) === true) {

                $id = (int)($_GET['id']);

                $ticket = $ticketRepo->getTicket($id);
                $editable = true;
                $mailer = new core\mailer();

                if (!empty($ticket)) {

                    $helper = new core\helper();
                    $file = new repositories\files();
                    $user = new repositories\users();
                    $comment = new repositories\comments();

                    $timesheets = new repositories\timesheets();
                    $allHours = 0;

                    //Upload File
                    if (isset($_POST['upload'])) {
                        $message = $this->uploadFile($_POST, $_FILES, $id);
                        $tpl->setNotification($message["msg"], $message["type"]);

                        $subject = "New file in ToDo [" . $ticket['id'] . "] - " . $ticket['headline'] . "";
                        $actual_link = "".CURRENT_URL."#files";
                        $message = "" . $_SESSION["userdata"]["name"] . " a new file in To-Do: ".$ticket['headline'];
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "[" . $ticket['id'] . "] - " . $ticket['headline'] . ""));

                    }

                    //Delete file
                    if (isset($_GET['delFile']) === true) {

                        $message = $this->deleteFile($_GET['delFile']);
                        $tpl->setNotification($message["msg"], $message["type"]);
                    }

                    //Add comment
                    if (isset($_POST['comment']) === true) {
                        $message = $this->addComment($_POST, $id);
                        $tpl->setNotification($message["msg"], $message["type"]);

                        $subject = "New comment in ToDo [" . $ticket['id'] . "] - " . $ticket['headline'] . "";
                        $actual_link = "".CURRENT_URL."#comments";
                        $message = "" . $_SESSION["userdata"]["name"] . " added a new comment to To-Do:
                        ".$_POST['text']."
                        ";
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "[" . $ticket['id'] . "] - " . $ticket['headline'] . ""));

                    }

                    //Delete comment
                    if (isset($_GET['delComment']) === true) {
                        $commentId = (int)($_GET['delComment']);
                        $comment->deleteComment($commentId);
                        $tpl->setNotification("Comment deleted", "success");
                    }

                    //Add Timesheet entry
                    if (isset($_POST['saveTimes']) === true) {
                        $message = $this->addTimes($id, $_POST);
                        $tpl->setNotification($message["msg"], $message["type"]);
                    }

                    if (isset($_POST["saveTicket"]) === true || isset($_POST["saveAndCloseTicket"]) === true) {

                        $message = $this->editTicket($_POST, $id, $ticket);

                        $tpl->setNotification($message["msg"], $message["type"]);
                        $ticket = $ticketRepo->getTicket($id);

                        $subject = "One of your To-Dos was updated. [" . $ticket['id'] . "] - " . $ticket['headline'] . "";
                        $actual_link = CURRENT_URL;
                        $message = "" . $_SESSION["userdata"]["name"] . " updated  To-Do ";
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "[" . $ticket['id'] . "] - " . $ticket['headline'] . ""));

                        if(isset($_POST["saveAndCloseTicket"]) === true) {

                            $tpl->redirect(BASE_URL.$_SESSION['lastPage']);
                        }
                    }

                    //Subtasks
                    if (isset($_POST['subtaskSave']) === true) {

                        $values = array(
                            'headline' => $_POST['headline'],
                            'type' => 'subtask',
                            'description' => $_POST['description'],
                            'projectId' => $ticket['projectId'],
                            'editorId' => $_SESSION['userdata']['id'],
                            'userId' => $_SESSION['userdata']['id'],

                            'date' => $helper->timestamp2date(date("Y-m-d H:i:s"), 2),
                            'dateToFinish' => "",
                            'status' => $_POST['status'],
                            'storypoints' => "",
                            'hourRemaining' => $_POST['hourRemaining'],
                            'planHours' => $_POST['planHours'],
                            'sprint' => "",
                            'acceptanceCriteria' => "",
                            'tags' => "",
                            'editFrom' => "",
                            'editTo' => "",
                            'dependingTicketId' => $ticket['id'],
                        );

                        if ($_POST['subtaskId'] == "new") {
                            //New Ticket
                            $subtaskId = $ticketRepo->addTicket($values);
                        } else {
                            //Update Ticket
                            $subtaskId = $_POST['subtaskId'];
                            $ticketRepo->updateTicket($values, $subtaskId);
                        }


                        $subTasks = $ticketRepo->getAllSubtasks($ticket['id']);
                        $sumPlanHours = 0;
                        $sumEstHours = 0;
                        foreach ($subTasks as $subticket) {
                            $sumPlanHours = $sumPlanHours + $subticket['planHours'];
                            $sumEstHours = $sumEstHours + $subticket['hourRemaining'];
                        }
                        $postTemp = $ticket;
                        $postTemp['hourRemaining'] = $sumEstHours;
                        $postTemp['planHours'] = $sumPlanHours;
                        $postTemp['project'] = $postTemp['projectId'];

                        $this->editTicket($postTemp, $id, $ticket);
                        $ticket = $ticketRepo->getTicket($id);

                    }

                    //Delete Subtask
                    if (isset($_POST['subtaskDelete']) === true) {
                        $subtaskId = $_POST['subtaskId'];
                        $ticketRepo->delTicket($subtaskId);

                        $subTasks = $ticketRepo->getAllSubtasks($ticket['id']);
                        $sumPlanHours = 0;
                        $sumEstHours = 0;
                        foreach ($subTasks as $subticket) {
                            $sumPlanHours = $sumPlanHours + $subticket['planHours'];
                            $sumEstHours = $sumEstHours + $subticket['hourRemaining'];
                        }
                        $postTemp = $ticket;
                        $postTemp['hourRemaining'] = $sumEstHours;
                        $postTemp['planHours'] = $sumPlanHours;
                        $postTemp['project'] = $postTemp['projectId'];

                        $this->editTicket($postTemp, $id, $ticket);
                        $ticket = $ticketRepo->getTicket($id);
                    }


                    //Prepare and assign variables

                    $timeSheetValues = array(
                        'userId' => $_SESSION['userdata']['id'],
                        'ticket' => $id,
                        'date' => '',
                        'kind' => '',
                        'hours' => '',
                        'description' => '',
                        'invoicedEmpl' => '',
                        'invoicedComp' => '',
                        'invoicedEmplDate' => '',
                        'invoicedCompDate' => ''
                    );

                    $tpl->assign("timesheetValues", $timeSheetValues);
                    $ticketHours = $timesheets->getTicketHours($id);
                    $tpl->assign('ticketHours', $ticketHours);
                    $tpl->assign('userHours', $timesheets->getUsersTicketHours($id, $_SESSION['userdata']['id']));

                    $userinfo = $user->getUser($_SESSION['userdata']['id']);
                    $tpl->assign('kind', $timesheets->kind);
                    $tpl->assign('userInfo', $userinfo);

                    $tpl->assign('userId', $_SESSION['userdata']['id']);

                    $results = $timesheets->getTicketHours($id);
                    $allHours = 0;
                    foreach ($results as $row) {
                        if ($row['summe']) {
                            $allHours += $row['summe'];
                        }
                    }

                    $tpl->assign('timesheetsAllHours', $allHours);

                    $remainingHours = $ticket['planHours'] - $allHours;
                    $tpl->assign('remainingHours', $remainingHours);

                    $unreadCount = count($ticketRepo->getUnreadTickets($_SESSION['userdata']['id']));
                    $tpl->assign('unreadCount', $unreadCount);

                    $tpl->assign('type', $ticketRepo->getType());
                    $tpl->assign('users', $projects->getUsersAssignedToProject($ticket['projectId']));

                    $tpl->assign('ticketHistory', $ticketRepo->getTicketHistory((int)$_GET['id']));

                    $tpl->assign('ticketPrice', $ticketRepo->getTicketCost($_GET['id']));
                    $tpl->assign('info', $msgKey);
                    $tpl->assign('role', $_SESSION['userdata']['role']);
                    $tpl->assign('ticket', $ticket);
                    $tpl->assign('objTicket', $ticketRepo);
                    $tpl->assign('state', $ticketRepo->state);
                    $tpl->assign('statePlain', $ticketRepo->statePlain);
                    $tpl->assign('editable', $editable);
                    $tpl->assign('helper', $helper);

                    $comments = $comment->getComments('ticket', $ticket['id']);
                    $tpl->assign('numComments', $comment->countComments('ticket', $ticket['id']));
                    $tpl->assign('comments', $comments);

                    $subTasks = $ticketRepo->getAllSubtasks($ticket['id']);
                    $tpl->assign('numSubTasks', count($subTasks));
                    $tpl->assign('allSubTasks', $subTasks);

                    $files = $file->getFilesByModule('ticket', $id);
                    $tpl->assign('files', $files);
                    $tpl->assign("milestones", $ticketService->getAllMilestones($_SESSION["currentProject"]));
                    $tpl->assign('numFiles', count($files));
                    $tpl->assign('efforts', $ticketRepo->efforts);
                    $tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
                    $tpl->assign("sprints", $sprintService->getAllSprints($_SESSION["currentProject"]));
                    $tpl->display('tickets.showTicket');

                } else {

                    $tpl->display('general.error');

                }

            } else {

                $tpl->display('general.error');

            }

        }

        public function uploadFile($post, $files, $id)
        {

            $message = array("msg" => "", "type" => "");
            $file = new repositories\files();

            if (isset($files['file'])) {

                if ($file->upload($files, 'ticket', $id) !== false) {

                    $message["msg"] = "FILE_UPLOADED";
                    $message["type"] = "success";

                } else {

                    $message["msg"] = "ERROR_WHILE_UPLOADING";
                    $message["type"] = "error";

                }

            } else {

                $message["msg"] = "NO_FILE";
                $message["type"] = "error";

            }

            return $message;

        }

        public function deleteFile($file)
        {
            //$file = $_GET['delFile'];
            $ticketRepo = new repositories\tickets();
            $upload = new fileupload();

            $upload->initFile($file);

            //Delete file from server
            $upload->deleteFile($file);

            //Delete file from db
            $ticketRepo->deleteFile($file);

            return array("msg" => "FILE_DELETED", "type" => "success");
        }

        public function setReadState($id)
        {

            $read = new read();
            if (!$read->isRead('ticket', $id, $_SESSION['userdata']['id'])) {
                $read->markAsRead('ticket', $id, $_SESSION['userdata']['id']);
            }

        }

        public function addComment($post, $id)
        {

            $comment = new repositories\comments();

            $values = array(
                'text' => $_POST['text'],
                'date' => date("Y-m-d H:i:s"),
                'userId' => ($_SESSION['userdata']['id']),
                'moduleId' => $id,
                'commentParent' => ($_POST['father'])
            );

            $comment->addComment($values, 'ticket');

            return array("msg" => "COMMENT_ADDED", "type" => "success");

        }

        public function addTimes($id, $post)
        {

            $helper = new core\helper();
            $user = new repositories\users();
            $timesheets = new repositories\timesheets();

            $userinfo = $user->getUser($_SESSION['userdata']['id']);

            $values = array(
                'userId' => $_SESSION['userdata']['id'],
                'ticket' => $id,
                'date' => '',
                'kind' => '',
                'hours' => '',
                'description' => '',
                'invoicedEmpl' => '',
                'invoicedComp' => '',
                'invoicedEmplDate' => '',
                'invoicedCompDate' => ''
            );

            if (isset($_POST['kind']) && $_POST['kind'] != '') {
                $values['kind'] = $_POST['kind'];
            }
            if (isset($_POST['date']) && $_POST['date'] != '') {
                $date = $helper->date2timestamp($_POST['date']);
                $values['date'] = $date;
            }

            $values['rate'] = $userinfo['wage'];

            if (isset($_POST['hours']) && $_POST['hours'] != '') {
                $values['hours'] = ($_POST['hours']);
            }

            if (isset($_POST['description']) && $_POST['description'] != '') {
                $values['description'] = $_POST['description'];
            }

            if ($values['kind'] != '') {

                if ($values['date'] != '') {

                    if ($values['hours'] != '' && $values['hours'] > 0) {

                        $timesheets->addTime($values);
                        return array("msg" => "TIME_SAVED", "type" => "success");

                    } else {
                        return array("msg" => "NO_HOURS", "type" => "error");
                    }
                } else {
                    return array("msg" => "NO_DATE", "type" => "error");
                }

            } else {
                return array("msg" => "NO_KIND", "type" => "error");
            }
        }

        public function editTicket($post, $id, &$ticket)
        {

            $helper = new core\helper();
            $ticketRepo = new repositories\tickets();

            $values = array(
                'id' => $id,
                'headline' => $post['headline'],
                'type' => $post['type'],
                'description' => $post['description'],
                'projectId' => $_SESSION['currentProject'],
                'editorId' => $post['editorId'],
                'date' => $helper->timestamp2date(date("Y-m-d H:i:s"), 2),
                'dateToFinish' => $post['dateToFinish'],
                'status' => $post['status'],
                'planHours' => $post['planHours'],
                'tags' => $post['tags'],
                'sprint' => $post['sprint'],
                'storypoints' => $post['storypoints'],
                'hourRemaining' => $post['hourRemaining'],
                'acceptanceCriteria' => $post['acceptanceCriteria'],
                'editFrom' => $post['editFrom'],
                'editTo' => $post['editTo'],
                'userFirstname' => $ticket['userFirstname'],
                'userLastname' => $ticket['userLastname'],
                'dependingTicketId' => $post['dependingTicketId']
            );

            if ($values['headline'] === '') {

                return array("msg" => "ERROR_NO_HEADLINE", "type" => "error");

            } else {

                //Prepare dates for db
                $values['date'] = $helper->date2timestamp($values['date']);
                $values['dateToFinish'] = $helper->date2timestamp($values['dateToFinish']);
                $values['editFrom'] = $helper->date2timestamp($values['editFrom']);
                $values['editTo'] = $helper->date2timestamp($values['editTo']);

                //Update Ticket

                $ticketRepo->updateTicket($values, $id);

                return array("msg" => "TICKET_EDIT_SUCCESS", "type" => "success");

            }
        }

    }

}
