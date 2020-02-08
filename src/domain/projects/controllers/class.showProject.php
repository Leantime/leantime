<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use JoliCode;
    use leantime\domain\services;

    class showProject
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function __construct () {
            $this->settingsRepo = new repositories\setting();
            $this->projectService = new services\projects();
            $this->language = new core\language();
            $this->commentService = new services\comments();

            if(!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = "/projects/showAll";
            }
        }


        public function run()
        {


            $tpl = new core\template();
            $projectRepo = new repositories\projects();
            $config = new core\config();

            if (isset($_GET['id'])) {

                $id = (int)($_GET['id']);

                //Mattermost integration
                if(isset($_POST['mattermostSave'])) {
                    $webhook = strip_tags($_POST['mattermostWebhookURL']);
                    $this->settingsRepo->saveSetting("projectsettings." . $id . ".mattermostWebhookURL", $webhook);
                    $tpl->setNotification($this->language->__("notification.saved_mattermost_webhook"), 'success');

                }

                //Slack integration
                if(isset($_POST['slackSave'])) {

                    $webhook = strip_tags($_POST['slackWebhookURL']);
                    $this->settingsRepo->saveSetting("projectsettings." . $id . ".slackWebhookURL", $webhook);
                    $tpl->setNotification($this->language->__("notification.saved_mattermost_webhook"), 'success');
                }


                //Zulip
                $zulipWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".zulipHook");

                if($zulipWebhook === false || $zulipWebhook == ""){

                    $zulipHook = array(
                        'zulipURL' => '',
                        'zulipEmail' => '',
                        'zulipBotKey' => '',
                        'zulipStream' => '',
                        'zulipTopic' => '',
                    );
                    $tpl->assign('zulipHook', $zulipHook);
                }else{
                    $tpl->assign('zulipHook', unserialize($zulipWebhook));
                }


                if(isset($_POST['zulipSave'])) {

                    $zulipHook = array(
                        'zulipURL' => strip_tags($_POST['zulipURL']),
                        'zulipEmail' => strip_tags($_POST['zulipEmail']),
                        'zulipBotKey' => strip_tags($_POST['zulipBotKey']),
                        'zulipStream' => strip_tags($_POST['zulipStream']),
                        'zulipTopic' => strip_tags($_POST['zulipTopic']),
                    );

                    if($zulipHook['zulipURL'] == "" ||
                        $zulipHook['zulipEmail'] == "" ||
                        $zulipHook['zulipBotKey'] == "" ||
                        $zulipHook['zulipStream'] == "" ||
                        $zulipHook['zulipTopic'] == "") {


                        $tpl->setNotification('Zulip integration could not be saved. Please fill out all the fields', 'error');

                    }else{

                        $this->settingsRepo->saveSetting("projectsettings." . $id . ".zulipHook", serialize($zulipHook));
                        $tpl->setNotification('Zulip integration saved successfully', 'success');
                    }

                    $tpl->assign('zulipHook', $zulipHook);


                }


                if(isset($_GET['integrationSuccess'])) {
                    $tpl->setNotification('Slack was successfully connected', 'success');
                }

                $mattermostWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".mattermostWebhookURL");
                $tpl->assign('mattermostWebhookURL', $mattermostWebhook);

                $slackWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".slackWebhookURL");
                $tpl->assign('slackWebhookURL', $slackWebhook);

                $_SESSION['lastPage'] = "/projects/showProject/".$id;
                
                $project = $projectRepo->getProject($id);
                $project['assignedUsers'] = $projectRepo->getProjectUserRelation($id);

                $helper = new core\helper();



                //Calculate projectdetails
                //TODO: Change to be from ticketRepo!!!
                $opentickets = $projectRepo->getOpenTickets($id);

                $closedTickets = $project['numberOfTickets'] - $opentickets['openTickets'];

                if ($project['numberOfTickets'] != 0) {

                    $projectPercentage = round($closedTickets / $project['numberOfTickets'] * 100, 2);
                } else {

                    $projectPercentage = 0;
                }

                if ($project['numberOfTickets'] == null) {
                    $project['numberOfTickets'] = 1;
                }


                if (isset($_POST['save']) === true) {

                    if (isset($_POST['editorId']) && count($_POST['editorId'])) {
                        $assignedUsers = $_POST['editorId'];
                    } else {
                        $assignedUsers = array();
                    }

                    $values = array(
                        'name' => $_POST['name'],
                        'details' => $_POST['details'],
                        'clientId' => $_POST['clientId'],
                        'state' => $_POST['projectState'],
                        'hourBudget' => $_POST['hourBudget'],
                        'assignedUsers' => $assignedUsers,
                        'dollarBudget' => $_POST['dollarBudget']
                    );

                    if ($values['name'] !== '') {

                        if ($projectRepo->hasTickets($id) && $values['state'] == 1) {

                            $tpl->setNotification($this->language->__("notification.project_has_tickets"), 'error');

                        } else {

                            $projectRepo->editProject($values, $id);

                            $project = $projectRepo->getProject($id);
                            $project['assignedUsers'] = $projectRepo->getProjectUserRelation($id);


                            //Take the old value to avoid nl character
                            $values['details'] = $_POST['details'];

                            $tpl->setNotification($this->language->__("notification.project_saved"), 'success');

                            $subject = sprintf($this->language->__("email_notifications.project_update_subject "), $id, $values['name']);
                            $message = sprintf($this->language->__("email_notifications.project_update_message"), $_SESSION["userdata"]["name"], $values['name']);
                            $linkLabel = $this->language->__("email_notifications.project_update_cta");

                            $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

                            $this->projectService->notifyProjectUsers($message, $subject, $id, array("link"=>$actual_link, "text"=> $linkLabel));

                        }

                    } else {

                        $tpl->setNotification($this->language->__("notification.no_project_name"), 'error');

                    }

                }

                //Post comment
                $comments = new repositories\comments();
                if (isset($_POST['comment']) === true) {

                    if($this->commentService->addComment($_POST, "project", $id, $project)) {

                        $tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                    }else {
                        $tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                    }
                }

                $file = new repositories\files();
                if (isset($_POST['upload'])) {
                    if (isset($_FILES['file']) === true && $_FILES['file']["tmp_name"] != "") {

                        $return = $file->upload($_FILES, 'project', $id);
                        $tpl->setNotification($this->language->__("notifications.file_upload_success"), 'success');

                    }else{

                        $tpl->setNotification($this->language->__("notifications.file_upload_error"), 'error');
                    }
                }


                $timesheets = new repositories\timesheets();



                $data = array();
                $months = array();
                $results = $timesheets->getProjectHours($id);


                $allHours = 0;
                $max = 0;
                foreach ($results as $row) {

                    if ($row['month'] != null) {

                        $data[] = (int)$row['summe'];
                        $months[] = substr($this->language->__('MONTH_' . $row['month'] . ''), 0, 3);

                        if ($row['summe'] > $max) {
                            $max = $row['summe'];
                        }

                    } else {

                        $allHours = $row['summe'];

                    }

                }


                $steps = 10;

                if ($max > 100) {
                    $steps = 50;
                }

                $max = $max + $steps;

                $tpl->assign('timesheetsAllHours', $allHours);

                $chart = "";

                $tpl->assign('chart', $chart);


                //Delete File
                if (isset($_GET['delFile']) === true) {

                    $file = $_GET['delFile'];
                    $upload = new core\fileupload();

                    $upload->initFile($file);
                    $upload->deleteFile($file);

                    $this->deleteFile($file);

                    $this->setNotification($this->language->__("notifications.file_deleted"), 'success');

                }

                //Delete comment
                if (isset($_GET['delComment']) === true) {

                    $commentId = (int)($_GET['delComment']);

                    $this->deleteComment($commentId);

                    $this->setNotification($this->language->__("notifications.comment_deleted"), "success");

                }
                //Timesheets
                $invEmplCheck = '0';
                $invCompCheck = '0';

                $projectFilter = $id;
                $dateFrom = mktime(0, 0, 0, date("m"), '1', date("Y"));
                $dateFrom = date("Y-m-d", $dateFrom);
                $dateTo = date("Y-m-d 00:00:00");
                $kind = 'all';
                $userId = 'all';


                if (isset($_POST['kind']) && $_POST['kind'] != '') {

                    $kind = ($_POST['kind']);

                }

                if (isset($_POST['userId']) && $_POST['userId'] != '') {

                    $userId = ($_POST['userId']);

                }

                if (isset($_POST['dateFrom']) && $_POST['dateFrom'] != '') {

                    $dateFrom = ($helper->timestamp2date($_POST['dateFrom'], 4));

                }

                if (isset($_POST['dateTo']) && $_POST['dateTo'] != '') {

                    $dateTo = ($helper->timestamp2date($_POST['dateTo'], 4));

                }

                if (isset($_POST['invEmpl']) === true) {

                    $invEmplCheck = $_POST['invEmpl'];

                    if ($invEmplCheck == 'on') {
                        $invEmplCheck = '1';
                    } else {
                        $invEmplCheck = '0';
                    }

                } else {
                    $invEmplCheck = '0';
                }

                if (isset($_POST['invComp']) === true) {

                    $invCompCheck = ($_POST['invComp']);

                    if ($invCompCheck == 'on') {
                        $invCompCheck = '1';
                    } else {
                        $invCompCheck = '0';
                    }

                } else {
                    $invCompCheck = '0';
                }

                $user = new repositories\users();
                $employees = $user->getEmployees();
                $timesheets = new repositories\timesheets();
                $projects = new repositories\projects();
                $clients = new repositories\clients();

                $user = new repositories\users();
                $tpl->assign('availableUsers', $user->getAll());

                $tpl->assign('employeeFilter', $userId);
                $tpl->assign('employees', $employees);
                $tpl->assign('dateFrom', $helper->timestamp2date($dateFrom, 2));
                $tpl->assign('dateTo', $helper->timestamp2date($dateTo, 2));
                $tpl->assign('actKind', $kind);
                $tpl->assign('kind', $timesheets->kind);
                $tpl->assign('invComp', $invCompCheck);
                $tpl->assign('invEmpl', $invEmplCheck);
                $tpl->assign('helper', $helper);
                $tpl->assign('projectFilter', $projectFilter);
                $tpl->assign('clients', $clients->getAll());

                $tpl->assign('allTimesheets', $timesheets->getAll($projectFilter, $kind, $dateFrom, $dateTo, $userId, $invEmplCheck, $invCompCheck));

                //Assign vars
                $ticket = new repositories\tickets();
                $tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
                $tpl->assign('projectTickets', $projectRepo->getProjectTickets($id));
                $tpl->assign('projectPercentage', $projectPercentage);
                $tpl->assign('openTickets', $opentickets['openTickets']);
                $tpl->assign('project', $project);

                $files = $file->getFilesByModule('project', $id);
                $tpl->assign('files', $files);
                $tpl->assign('numFiles', count($files));

                $bookedHours = $projectRepo->getProjectBookedHours($id);
                if ($bookedHours['totalHours'] != '') {
                    $booked = round($bookedHours['totalHours'], 3);
                } else {
                    $booked = 0;
                }

                $tpl->assign('bookedHours', $booked);

                $bookedDollars = $projectRepo->getProjectBookedDollars($id);
                if ($bookedDollars['totalDollars'] != '') {
                    $dollars = round($bookedDollars['totalDollars'], 3);
                } else {
                    $dollars = 0;
                }

                $tpl->assign('bookedDollars', $dollars);

                $tpl->assign("bookedHoursArray", $projectRepo->getProjectBookedHoursArray($id));

                $comment = $comments->getComments('project', $_GET['id']);
                $tpl->assign('comments', $comment);
                $tpl->assign('numComments', $comments->countComments('project', $_GET['id']));


                $tpl->assign('state', $projectRepo->state);
                $tpl->assign('helper', $helper);
                $tpl->assign('role', $_SESSION['userdata']['role']);
                $accounts = $projectRepo->getProjectAccounts($id);
                $tpl->assign('accounts', $accounts);


                $tpl->display('projects.showProject');

            } else {

                $tpl->display('general.error');

            }

        }

        private function generateOfcData()
        {

        }

    }

}
