<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showProject
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function __construct () {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager], true);

            $this->settingsRepo = new repositories\setting();
            $this->projectService = new services\projects();
            $this->language = new core\language();
            $this->commentService = new services\comments();
            $this->fileService = new services\files();
            $this->ticketService = new services\tickets();

            if(!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = CURRENT_URL;
            }


        }


		/**
		 * One Method to rule them all...
		 */
        public function run()
        {

            $tpl = new core\template();
            $projectRepo = new repositories\projects();
            $config = new core\config();

            if (isset($_GET['id']) === true) {

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
                    $tpl->setNotification($this->language->__("notification.saved_slack_webhook"), 'success');
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


                        $tpl->setNotification($this->language->__("notification.error_zulip_webhook_fill_out_fields"), 'error');

                    }else{

                        $this->settingsRepo->saveSetting("projectsettings." . $id . ".zulipHook", serialize($zulipHook));
                        $tpl->setNotification($this->language->__("notification.saved_zulip_webhook"), 'success');
                    }

                    $tpl->assign('zulipHook', $zulipHook);


                }

                //Discord integration; provide three possible webhooks per project
                if (isset($_POST['discordSave'])) {
                  for ($i = 1; 3 >= $i ; $i++) {
                    $webhook = trim(strip_tags($_POST['discordWebhookURL' . $i]));
                    $this->settingsRepo->saveSetting('projectsettings.' . $id . '.discordWebhookURL' . $i, $webhook);
                  }
                  $tpl->setNotification($this->language->__('notification.saved_discord_webhook'), 'success');
                }

                $mattermostWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".mattermostWebhookURL");
                $tpl->assign('mattermostWebhookURL', $mattermostWebhook);

                $slackWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".slackWebhookURL");
                $tpl->assign('slackWebhookURL', $slackWebhook);

                for ($i = 1; 3 >= $i ; $i++) {
                  $discordWebhook = $this->settingsRepo->getSetting('projectsettings.' . $id . '.discordWebhookURL' . $i);
                  $tpl->assign('discordWebhookURL' . $i, $discordWebhook);
                }

                $_SESSION["projectsettings"]['commentOrder'] = $this->settingsRepo->getSetting("projectsettings." . $id . ".commentOrder");
                $_SESSION["projectsettings"]['ticketLayout'] = $this->settingsRepo->getSetting("projectsettings." . $id . ".ticketLayout");


                $_SESSION['lastPage'] = BASE_URL."/projects/showProject/".$id;
                
                $project = $projectRepo->getProject($id);
                $project['assignedUsers'] = $projectRepo->getProjectUserRelation($id);

                if(isset($_POST['submitSettings'])) {

                    if(isset($_POST['labelKeys']) && is_array($_POST['labelKeys']) && count($_POST['labelKeys']) > 0){

                        if($this->ticketService->saveStatusLabels($_POST)){

                            $tpl->setNotification($this->language->__('notification.new_status_saved'), 'success');
                        }else{
                            $tpl->setNotification($this->language->__('notification.error_saving_status'), 'error');
                        }

                    }else{
                        $tpl->setNotification($this->language->__('notification.at_least_one_status'), 'error');
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

                    $projectRepo->editProjectRelations($values, $id);

                    $project = $projectRepo->getProject($id);
                    $project['assignedUsers'] = $projectRepo->getProjectUserRelation($id);

                    $tpl->setNotification($this->language->__("notifications.user_was_added_to_project"), "success");


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

                        if ($projectRepo->hasTickets($id) && $values['state'] == 1) {

                            $tpl->setNotification($this->language->__("notification.project_has_tickets"), 'error');

                        } else {

                            $projectRepo->editProject($values, $id);

                            $project = $projectRepo->getProject($id);
                            $project['assignedUsers'] = $projectRepo->getProjectUserRelation($id);


                            //Take the old value to avoid nl character
                            $values['details'] = $_POST['details'];

                            $tpl->setNotification($this->language->__("notification.project_saved"), 'success');

                            $subject = sprintf($this->language->__("email_notifications.project_update_subject"), $id, $values['name']);
                            $message = sprintf($this->language->__("email_notifications.project_update_message"), $_SESSION["userdata"]["name"], $values['name']);
                            $linkLabel = $this->language->__("email_notifications.project_update_cta");

                            $actual_link = CURRENT_URL;

                            $this->projectService->notifyProjectUsers($message, $subject, $id, array("link"=>$actual_link, "text"=> $linkLabel));

                        }

                    } else {

                        $tpl->setNotification($this->language->__("notification.no_project_name"), 'error');

                    }

                }

                // Manage Post comment
                $comments = new repositories\comments();
                if (isset($_POST['comment']) === true) {

                    if($this->commentService->addComment($_POST, "project", $id, $project)) {

                        $tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                    }else {
                        $tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                    }
                }

                //Manage File Uploads
                $file = new repositories\files();
                if (isset($_POST['upload'])) {
                    if (isset($_FILES['file']) === true && $_FILES['file']["tmp_name"] != "") {

                        $return = $file->upload($_FILES, 'project', $id);
                        $tpl->setNotification($this->language->__("notifications.file_upload_success"), 'success');

                    }else{

                        $tpl->setNotification($this->language->__("notifications.file_upload_error"), 'error');
                    }
                }

                //Delete File
                if (isset($_GET['delFile']) === true) {

                    $result = $this->fileService->deleteFile($_GET['delFile']);

                    if($result === true) {
                        $tpl->setNotification($this->language->__("notifications.file_deleted"), "success");
                        $tpl->redirect(BASE_URL."/projects/showProject/".$id."#files");
                    }else {
                        $tpl->setNotification($result["msg"], "success");
                    }

                }

                //Delete comment
                if (isset($_GET['delComment']) === true) {

                    $commentId = (int)($_GET['delComment']);

                    $comments->deleteComment($commentId);

                    $tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");

                }




                $user = new repositories\users();
                $employees = $user->getEmployees();
                $timesheets = new repositories\timesheets();
                $projects = new repositories\projects();
                $clients = new repositories\clients();

                $user = new repositories\users();


                $tpl->assign('availableUsers', $user->getAll());
                $tpl->assign('clients', $clients->getAll());


                $tpl->assign("todoStatus", $this->ticketService->getStatusLabels());

                $tpl->assign('employees', $employees);


                //Assign vars
                $ticket = new repositories\tickets();
                $tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
                $tpl->assign('projectTickets', $projectRepo->getProjectTickets($id));

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

                $comment = $comments->getComments('project', $_GET['id'],"", $_SESSION["projectsettings"]['commentOrder']);
                $tpl->assign('comments', $comment);
                $tpl->assign('numComments', $comments->countComments('project', $_GET['id']));


                $tpl->assign('state', $projectRepo->state);
                $tpl->assign('role', $_SESSION['userdata']['role']);

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
