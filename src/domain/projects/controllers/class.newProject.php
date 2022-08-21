<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class newProject
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager], true);

            if(!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = BASE_URL."/projects/showAll";
            }

            $tpl = new core\template();
            $projectRepo = new repositories\projects();
            $leancanvasRepo = new repositories\leancanvas();
            $ideaRepo = new repositories\ideas();
            $ticketService = new services\tickets();
            $projectService = new services\projects();
            $language = new core\language();


            $msgKey = '';
            $values = array(
                'id' => '',
                'name' => '',
                'details' => '',
                'clientId' => '',
                'hourBudget' => '',
                'assignedUsers' => array($_SESSION['userdata']['id']),
                'dollarBudget' => '',
                'state' => '',
                'psettings' => ''
            );

            if (isset($_POST['save']) === true) {

                if (!isset($_POST['hourBudget']) || $_POST['hourBudget'] == '' || $_POST['hourBudget'] == null) {
                    $hourBudget = '0';
                } else {
                    $hourBudget = $_POST['hourBudget'];
                }


                if (isset($_POST['editorId']) && count($_POST['editorId'])) {
                    $assignedUsers = $_POST['editorId'];
                } else {
                    $assignedUsers = array();
                }


                $mailer = new core\mailer();

                $values = array(
                    'name' => $_POST['name'],
                    'details' => $_POST['details'],
                    'clientId' => $_POST['clientId'],
                    'hourBudget' => $hourBudget,
                    'assignedUsers' => $assignedUsers,
                    'dollarBudget' => $_POST['dollarBudget'],
                    'state' => $_POST['projectState'],
                    'psettings' => $_POST['globalProjectUserAccess']
                );

                if ($values['name'] === '') {

                    $tpl->setNotification($language->__("notification.no_project_name"), 'error');

                } elseif ($values['clientId'] === '') {

                    $tpl->setNotification($language->__("notification.no_client"), 'error');

                } else {

                    $projectName = $values['name'];
                    $id = $projectRepo->addProject($values);
                    $projectService->changeCurrentSessionProject($id);

                    $users = $projectRepo->getUsersAssignedToProject($id);

                    $mailer->setSubject($language->__('email_notifications.project_created_subject'));
                    $actual_link = BASE_URL."/projects/showProject/" . $id . "";
                    $message = sprintf($language->__('email_notifications.project_created_message'), $actual_link, $id, $projectName, $_SESSION["userdata"]["name"]);
                    $mailer->setHtml($message);

                    $to = array();

                    foreach ($users as $user) {

                        if ($user["notifications"] != 0) {
                            $to[] = $user["username"];
                        }
                    }

                    //$mailer->sendMail($to, $_SESSION["userdata"]["name"]);
	            // NEW Queuing messaging system
	            $queue = new repositories\queue();
                    $queue->queueMessageToUsers($to, $message, $language->__('email_notifications.project_created_subject'), $id);


                    //Take the old value to avoid nl character
                    $values['details'] = $_POST['details'];

                    $tpl->setNotification(sprintf($language->__('notifications.project_created_successfully'), BASE_URL.'/leancanvas/simpleCanvas/'), 'success');

                    $tpl->redirect(BASE_URL."/projects/showProject/". $id);

                }


                $tpl->assign('values', $values);

            }


            $tpl->assign('project', $values);
            $user = new repositories\users();
            $clients = new repositories\clients();




           $tpl->assign('availableUsers', $user->getAll());
           $tpl->assign('clients', $clients->getAll());


            $tpl->assign('info', $msgKey);

            $tpl->display('projects.newProject');


        }

    }

}
