<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class newTicket
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

            if(!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = BASE_URL."/tickets/showKanban/";
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
            $ticketRepo = new repositories\tickets();

            $helper = new core\helper();
            $projectObj = new repositories\projects();
            $user = new repositories\users();
            $language = new core\language();
            $mailer = new core\mailer();
            $sprintService = new services\sprints();
            $ticketService = new services\tickets();

            //Set current project ID
            if (isset($_COOKIE['searchCriteria']) === true) {
                $searchCriteria = unserialize($_COOKIE['searchCriteria']);

            }


            $msgKey = '';
            $userinfo = $user->getUser($_SESSION['userdata']['id']);

            $values = array(
                'headline' => "",
                'type' => "",
                'description' => "",
                'projectId' => $_SESSION['currentProject'],
                'editorId' => "",
                'userId' => $_SESSION['userdata']['id'],
                'userFirstname' => $userinfo["firstname"],
                'userLastname' => $userinfo["lastname"],
                'date' => $language->getFormattedDateString(date("Y-m-d H:i:s")),
                'dateToFinish' => "",
                'status' => 3,
                'planHours' => "0",
                'sprint' => "",
                'storypoints' => "",
                'hourRemaining' => "",
                'acceptanceCriteria' => "",
                'tags' => "",
                'editFrom' => "",
                'editTo' => "",
                'dependingTicketId' => ''
            );

            if (isset($_POST['saveTicket']) || isset($_POST['saveAndCloseTicket'])) {

                $values = array(
                    'headline' => $_POST['headline'],
                    'type' => $_POST['type'],
                    'description' => $_POST['description'],
                    'projectId' => $_SESSION['currentProject'],
                    'editorId' => $_POST['editorId'],
                    'userId' => $_SESSION['userdata']['id'],
                    'userFirstname' => $userinfo["firstname"],
                    'userLastname' => $userinfo["lastname"],
                    'date' => $helper->timestamp2date(date("Y-m-d H:i:s"), 2),
                    'dateToFinish' => $_POST['dateToFinish'],
                    'status' => $_POST['status'],
                    'storypoints' => $_POST['storypoints'],
                    'hourRemaining' => $_POST['hourRemaining'],
                    'planHours' => $_POST['planHours'],
                    'sprint' => $_POST['sprint'],
                    'acceptanceCriteria' => $_POST['acceptanceCriteria'],
                    'tags' => $_POST['tags'],
                    'editFrom' => $_POST['editFrom'],
                    'editTo' => $_POST['editTo'],
                    'dependingTicketId' => $_POST['dependingTicketId']
                );

                if ($values['headline'] === '') {

                    $tpl->setNotification('ERROR_NO_HEADLINE', 'error');

                } elseif ($values['projectId'] === '') {

                    $tpl->setNotification('ERROR_NO_PROJECT', 'error');

                } else {

                    $values['date'] = $helper->timestamp2date($values['date'], 4);
                    $values['dateToFinish'] = $helper->timestamp2date($values['dateToFinish'], 4);
                    $values['editFrom'] = $helper->timestamp2date($values['editFrom'], 4);
                    $values['editTo'] = $helper->timestamp2date($values['editTo'], 4);

                    // returns last inserted id
                    $id = $ticketRepo->addTicket($values);

                    $_SESSION['msg'] = "NEW_TICKET_ADDED";
                    $_SESSION['msgT'] = "success";


                    $subject = "New To-Do has been added to one of your projects.";
                    $actual_link = BASE_URL."/tickets/showTicket/". $id;
                    $message = "" . $_SESSION["userdata"]["name"] . " added a new To-Do to one of your projects: '".$values['headline']."'";
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));


                    $tpl->setNotification('To-Do created successfully', 'success');

                    if (isset($_POST['saveTicket'])) {
                        $tpl->redirect(BASE_URL."/tickets/showTicket/" . $id);
                    }


                    if(isset($_POST["saveAndCloseTicket"]) === true) {
                        $tpl->redirect($_SESSION['lastPage']);
                    }
                }

            }

            $tpl->assign('ticket', $values);

            $tpl->assign("sprints", $sprintService->getAllSprints($_SESSION["currentProject"]));
            $tpl->assign("milestones", $ticketService->getAllMilestones($_SESSION["currentProject"]));
            $tpl->assign('role', $_SESSION['userdata']['role']);
            $tpl->assign('users', $ticketRepo->getAvailableUsersForTicket());
            $tpl->assign('type', $ticketRepo->getType());
            $tpl->assign('info', $msgKey);
            $tpl->assign('efforts', $ticketRepo->efforts);
            $allprojects = $projectObj->getUserProjects();
            $tpl->assign('allProjects', $allprojects);
            $tpl->assign('type', $ticketRepo->getType());
            $tpl->assign('objTicket', $ticketRepo);
            $tpl->assign('employees', $user->getEmployees());
            $tpl->assign('timesheetsAllHours', 0);

            $tpl->assign('helper', $helper);

            $tpl->display('tickets.newTicket');

        }


        public function get () {

            $ticket = new models\tickets(
                array(
                    "userLastname"=>$_SESSION['userdata']["name"],
                    "status"=>3,
                    "projectId"=>$_SESSION['currentProject']
                )
            );

            $ticket->date =  $this->language->getFormattedDateString(date("Y-m-d H:i:s"));

            $this->tpl->assign('ticket', $ticket);
            $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
            $this->tpl->assign('ticketTypes', $this->ticketService->getTicketTypes());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('milestones', $this->ticketService->getAllMilestones($_SESSION["currentProject"]));
            $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));

            $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
            $this->tpl->assign('ticketHours', 0);
            $this->tpl->assign('userHours', 0);

            $this->tpl->assign('timesheetsAllHours', 0);
            $this->tpl->assign('remainingHours', 0);

            $this->tpl->assign('userInfo', $this->userService->getUser($_SESSION['userdata']['id']));
            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));

            $this->tpl->display('tickets.newTicket');


        }

        public function post ($params) {

            if (isset($params['saveTicket']) || isset($params['saveAndCloseTicket'])) {

                $result = $this->ticketService->addTicket($params);

                if(is_array($result) === false) {

                    $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");

                    if(isset($params["saveAndCloseTicket"]) === true) {

                        $this->tpl->redirect($_SESSION['lastPage']);

                    }else {

                        $this->tpl->redirect("/tickets/showTicket/".$result);
                    }

                }else {

                    $this->tpl->setNotification($this->language->__($result["msg"]), "error");

                    $ticket = new models\tickets($params);
                    $ticket->userLastname = $_SESSION['userdata']["name"];

                    $this->tpl->assign('ticket',$ticket);
                    $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
                    $this->tpl->assign('ticketTypes', $this->ticketService->getTicketTypes());
                    $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
                    $this->tpl->assign('milestones', $this->ticketService->getAllMilestones($_SESSION["currentProject"]));
                    $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));

                    $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
                    $this->tpl->assign('ticketHours', 0);
                    $this->tpl->assign('userHours', 0);

                    $this->tpl->assign('timesheetsAllHours', 0);
                    $this->tpl->assign('remainingHours', 0);

                    $this->tpl->assign('userInfo', $this->userService->getUser($_SESSION['userdata']['id']));
                    $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));

                    $this->tpl->display('tickets.newTicket');

                }

            }

        }

    }

}
