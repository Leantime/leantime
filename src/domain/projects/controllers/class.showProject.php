<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showProject extends controller
    {

        //services
        private $projectService;
        private $commentService;
        private $fileService;
        private $ticketService;

        // repositories
        private $settingsRepo;
        private $projectRepo;
        private $userRepo;
        private $clientsRepo;
        private $fileRepo;
        private $commentsRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init() {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager]);

            //services
            $this->projectService = new services\projects();
            $this->commentService = new services\comments();
            $this->fileService = new services\files();
            $this->ticketService = new services\tickets();

            // repositories
            $this->settingsRepo = new repositories\setting();
            $this->projectRepo = new repositories\projects();
            $this->userRepo = new repositories\users();
            $this->clientsRepo = new repositories\clients();
            $this->fileRepo = new repositories\files();
            $this->commentsRepo = new repositories\comments();

            if(!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = CURRENT_URL;
            }

        }


		/**
		 * One Method to rule them all...
		 */
        public function run()
        {

            if (isset($_GET['id']) === true) {

                $id = (int)($_GET['id']);

                //Mattermost integration
                if(isset($_POST['mattermostSave'])) {
                    $webhook = strip_tags($_POST['mattermostWebhookURL']);
                    $this->settingsRepo->saveSetting("projectsettings." . $id . ".mattermostWebhookURL", $webhook);
                    $this->tpl->setNotification($this->language->__("notification.saved_mattermost_webhook"), 'success');

                }

                //Slack integration
                if(isset($_POST['slackSave'])) {

                    $webhook = strip_tags($_POST['slackWebhookURL']);
                    $this->settingsRepo->saveSetting("projectsettings." . $id . ".slackWebhookURL", $webhook);
                    $this->tpl->setNotification($this->language->__("notification.saved_slack_webhook"), 'success');
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
                    $this->tpl->assign('zulipHook', $zulipHook);
                }else{
                    $this->tpl->assign('zulipHook', unserialize($zulipWebhook));
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


                        $this->tpl->setNotification($this->language->__("notification.error_zulip_webhook_fill_out_fields"), 'error');

                    }else{

                        $this->settingsRepo->saveSetting("projectsettings." . $id . ".zulipHook", serialize($zulipHook));
                        $this->tpl->setNotification($this->language->__("notification.saved_zulip_webhook"), 'success');
                    }

                    $this->tpl->assign('zulipHook', $zulipHook);


                }

                //Discord integration; provide three possible webhooks per project
                if (isset($_POST['discordSave'])) {
                  for ($i = 1; 3 >= $i ; $i++) {
                    $webhook = trim(strip_tags($_POST['discordWebhookURL' . $i]));
                    $this->settingsRepo->saveSetting('projectsettings.' . $id . '.discordWebhookURL' . $i, $webhook);
                  }
                  $this->tpl->setNotification($this->language->__('notification.saved_discord_webhook'), 'success');
                }

                $mattermostWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".mattermostWebhookURL");
                $this->tpl->assign('mattermostWebhookURL', $mattermostWebhook);

                $slackWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".slackWebhookURL");
                $this->tpl->assign('slackWebhookURL', $slackWebhook);

                for ($i = 1; 3 >= $i ; $i++) {
                  $discordWebhook = $this->settingsRepo->getSetting('projectsettings.' . $id . '.discordWebhookURL' . $i);
                  $this->tpl->assign('discordWebhookURL' . $i, $discordWebhook);
                }

                $_SESSION["projectsettings"]['commentOrder'] = $this->settingsRepo->getSetting("projectsettings." . $id . ".commentOrder");
                $_SESSION["projectsettings"]['ticketLayout'] = $this->settingsRepo->getSetting("projectsettings." . $id . ".ticketLayout");


                $_SESSION['lastPage'] = BASE_URL."/projects/showProject/".$id;

                $project = $this->projectRepo->getProject($id);
                $project['assignedUsers'] = $this->projectRepo->getProjectUserRelation($id);

                if(isset($_POST['submitSettings'])) {

                    if(isset($_POST['labelKeys']) && is_array($_POST['labelKeys']) && count($_POST['labelKeys']) > 0){

                        if($this->ticketService->saveStatusLabels($_POST)){

                            $this->tpl->setNotification($this->language->__('notification.new_status_saved'), 'success');
                        }else{
                            $this->tpl->setNotification($this->language->__('notification.error_saving_status'), 'error');
                        }

                    }else{
                        $this->tpl->setNotification($this->language->__('notification.at_least_one_status'), 'error');
                    }
                }


                if (isset($_POST['saveUsers']) === true) {

                    if (isset($_POST['editorId']) && count($_POST['editorId'])) {
                        $assignedUsers = $_POST['editorId'];
                    } else {
                        $assignedUsers = array();
                    }

                    $values = array(
                        "assignedUsers"=>$assignedUsers,
                        "projectRoles" => $_POST
                    );

                    $this->projectRepo->editProjectRelations($values, $id);

                    $project = $this->projectRepo->getProject($id);
                    $project['assignedUsers'] = $this->projectRepo->getProjectUserRelation($id);

                    $this->tpl->setNotification($this->language->__("notifications.user_was_added_to_project"), "success");


                }

                //save changed project data
                if (isset($_POST['save']) === true) {

                    $this->settingsRepo->saveSetting("projectsettings." . $id . ".commentOrder", $_POST['settingsCommentOrder']);
                    $this->settingsRepo->saveSetting("projectsettings." . $id . ".ticketLayout", $_POST['settingsTicketLayout']);

                    $_SESSION["projectsettings"]['commentOrder'] = $this->settingsRepo->getSetting("projectsettings." . $id . ".commentOrder");
                    $_SESSION["projectsettings"]['ticketLayout'] = $this->settingsRepo->getSetting("projectsettings." . $id . ".ticketLayout");


                    //bind Post Data into one array
                    $values = array(
                        'name' => $_POST['name'],
                        'details' => $_POST['details'],
                        'clientId' => $_POST['clientId'],
                        'state' => $_POST['projectState'],
                        'hourBudget' => $_POST['hourBudget'],
						'dollarBudget' => $_POST['dollarBudget'],
                        'psettings' => $_POST['globalProjectUserAccess']
                    );

                    if ($values['name'] !== '') {

                        if ($this->projectRepo->hasTickets($id) && $values['state'] == 1) {

                            $this->tpl->setNotification($this->language->__("notification.project_has_tickets"), 'error');

                        } else {

                            $this->projectRepo->editProject($values, $id);

                            $project = $this->projectRepo->getProject($id);
                            $project['assignedUsers'] = $this->projectRepo->getProjectUserRelation($id);


                            //Take the old value to avoid nl character
                            $values['details'] = $_POST['details'];

                            $this->tpl->setNotification($this->language->__("notification.project_saved"), 'success');

                            $subject = sprintf($this->language->__("email_notifications.project_update_subject"), $id, $values['name']);
                            $message = sprintf($this->language->__("email_notifications.project_update_message"), $_SESSION["userdata"]["name"], $values['name']);
                            $linkLabel = $this->language->__("email_notifications.project_update_cta");

                            $actual_link = CURRENT_URL;

                            $this->projectService->notifyProjectUsers($message, $subject, $id, array("link"=>$actual_link, "text"=> $linkLabel));

                        }

                    } else {

                        $this->tpl->setNotification($this->language->__("notification.no_project_name"), 'error');

                    }

                }

                // Manage Post comment
                if (isset($_POST['comment']) === true) {

                    if($this->commentService->addComment($_POST, "project", $id, $project)) {

                        $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                    }else {
                        $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                    }
                }

                //Manage File Uploads
                if (isset($_POST['upload'])) {
                    if (isset($_FILES['file']) === true && $_FILES['file']["tmp_name"] != "") {

                        $return = $this->fileRepo->upload($_FILES, 'project', $id);
                        $this->tpl->setNotification($this->language->__("notifications.file_upload_success"), 'success');

                    }else{

                        $this->tpl->setNotification($this->language->__("notifications.file_upload_error"), 'error');
                    }
                }

                //Delete File
                if (isset($_GET['delFile']) === true) {

                    $result = $this->fileService->deleteFile($_GET['delFile']);

                    if($result === true) {
                        $this->tpl->setNotification($this->language->__("notifications.file_deleted"), "success");
                        $this->tpl->redirect(BASE_URL."/projects/showProject/".$id."#files");
                    }else {
                        $this->tpl->setNotification($result["msg"], "success");
                    }

                }

                //Delete comment
                if (isset($_GET['delComment']) === true) {

                    $commentId = (int)($_GET['delComment']);

                    $this->commentsRepo->deleteComment($commentId);

                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");

                }

                $employees = $this->userRepo->getEmployees();

                $this->tpl->assign('availableUsers', $this->userRepo->getAll());
                $this->tpl->assign('clients', $this->clientsRepo->getAll());


                $this->tpl->assign("todoStatus", $this->ticketService->getStatusLabels());

                $this->tpl->assign('employees', $employees);

                //Assign vars
                $this->tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
                $this->tpl->assign('projectTickets', $this->projectRepo->getProjectTickets($id));

                $this->tpl->assign('project', $project);

                $files = $this->fileRepo->getFilesByModule('project', $id);
                $this->tpl->assign('files', $files);
                $this->tpl->assign('numFiles', count($files));

                $bookedHours = $this->projectRepo->getProjectBookedHours($id);
                if ($bookedHours['totalHours'] != '') {
                    $booked = round($bookedHours['totalHours'], 3);
                } else {
                    $booked = 0;
                }

                $this->tpl->assign('bookedHours', $booked);

                $bookedDollars = $this->projectRepo->getProjectBookedDollars($id);
                if ($bookedDollars['totalDollars'] != '') {
                    $dollars = round($bookedDollars['totalDollars'], 3);
                } else {
                    $dollars = 0;
                }

                $this->tpl->assign('bookedDollars', $dollars);

                $this->tpl->assign("bookedHoursArray", $this->projectRepo->getProjectBookedHoursArray($id));

                $comment = $this->commentsRepo->getComments('project', $_GET['id'],"", $_SESSION["projectsettings"]['commentOrder']);
                $this->tpl->assign('comments', $comment);
                $this->tpl->assign('numComments', $this->commentsRepo->countComments('project', $_GET['id']));


                $this->tpl->assign('state', $this->projectRepo->state);
                $this->tpl->assign('role', $_SESSION['userdata']['role']);

                $this->tpl->display('projects.showProject');

            } else {

                $this->tpl->display('general.error');

            }

        }

        private function generateOfcData()
        {

        }

    }

}
