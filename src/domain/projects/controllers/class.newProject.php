<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class newProject
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            if(!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = "/projects/showAll";
            }

            $tpl = new core\template();
            $projectRepo = new repositories\projects();
            $leancanvasRepo = new repositories\leancanvas();
            $ideaRepo = new repositories\ideas();
            $ticketService = new services\tickets();
            $projectService = new services\projects();

            $msgKey = '';
            $values = array(
                'id' => '',
                'name' => '',
                'details' => '<strong>Summary</strong><br /><i>{{Describe the project in a few words}}</i><br /><br/><strong>Business Justification</strong><br /><i>{{Why are you doing this project?}}</i><br /><br/><strong>Objectives/Goals</strong><ul><li><i>{{What are your goals with this project?}}</i></li></ul><br /><br/>  ',
                'clientId' => '',
                'hourBudget' => '',
                'assignedUsers' => array($_SESSION['userdata']['id']),
                'dollarBudget' => '',
                'state' => ''
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
                );

                if ($values['name'] === '') {

                    $msgKey = 'NO_PROJECTNAME';
                    $tpl->setNotification('NO_PROJECTNAME', 'error');

                } elseif ($values['clientId'] === '') {

                    $msgKey = 'ERROR_NO_CLIENT';
                    $tpl->setNotification('ERROR_NO_CLIENT', 'error');

                } else {

                    $projectName = $values['name'];
                    $id = $projectRepo->addProject($values);
                    $projectService->changeCurrentSessionProject($id);

                    $users = $projectRepo->getUsersAssignedToProject($id);

                    $mailer->setSubject("You have been added to a new project");
                    $actual_link = CURRENT_URL;
                    $mailer->setHtml("A new project was created and you are on it! Project name is <a href='" . $actual_link . "/projects/showProject/" . $id . "/'>[" . $id . "] - " . $projectName . "</a> and it was created by " . $_SESSION["userdata"]["name"] . "<br />");

                    $to = array();

                    foreach ($users as $user) {

                        if ($user["notifications"] != 0) {
                            $to[] = $user["username"];
                        }
                    }

                    $mailer->sendMail($to, $_SESSION["userdata"]["name"]);

                    //Take the old value to avoid nl character
                    $values['details'] = $_POST['details'];

                    $msgKey = 'PROJECT_ADDED';
                    $tpl->setNotification('Your new project was created successfully. Go to <a href="'.BASE_URL.'/leancanvas/simpleCanvas/">Research</a> to continue your journey.', 'success');

                    $tpl->redirect(BASE_URL."/projects/showProject/". $id);

                }


                $tpl->assign('values', $values);

            }


            $tpl->assign('project', $values);
            $user = new repositories\users();
            $tpl->assign('availableUsers', $user->getAll());

            $clients = new repositories\clients();

            $tpl->assign('info', $msgKey);
            $tpl->assign('clients', $clients->getAll());

            $tpl->display('projects.newProject');


        }

    }

}
