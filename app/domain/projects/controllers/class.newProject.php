<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;
    use leantime\plugins\services\billing;

    class newProject extends controller
    {
        private repositories\projects $projectRepo;
        private repositories\menu $menuRepo;
        private repositories\users $userRepo;
        private repositories\clients $clientsRepo;
        private repositories\queue $queueRepo;
        private services\projects $projectService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            repositories\projects $projectRepo,
            repositories\menu $menuRepo,
            repositories\users $userRepo,
            repositories\clients $clientsRepo,
            repositories\queue $queueRepo,
            services\projects $projectService
        ) {
            $this->projectRepo = $projectRepo;
            $this->menuRepo = $menuRepo;
            $this->userRepo = $userRepo;
            $this->clientsRepo = $clientsRepo;
            $this->queueRepo = $queueRepo;
            $this->projectService = $projectService;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager], true);

            if (!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = BASE_URL . "/projects/showAll";
            }

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
                'menuType' => repositories\menu::DEFAULT_MENU,
                'type' => 'project',
                'parent' => (int)$_GET['parent'] ?? '',
                'psettings' => '',
                'start' => '',
                'end' => '',
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


                $mailer = app()->make(core\mailer::class);

                $values = array(
                    'name' => $_POST['name'],
                    'details' => $_POST['details'],
                    'clientId' => $_POST['clientId'],
                    'hourBudget' => $hourBudget,
                    'assignedUsers' => $assignedUsers,
                    'dollarBudget' => $_POST['dollarBudget'] ?? 0,
                    'state' => $_POST['projectState'],
                    'psettings' => $_POST['globalProjectUserAccess'],
                    'menuType' => $_POST['menuType'] ?? 'default',
                    'type' => $_POST['type']  ?? 'project',
                    'parent' => $_POST['parent'] ?? '',
                    'start' => $this->language->getISODateString($_POST['start']),
                    'end' => $_POST['end'] ? $this->language->getISODateString($_POST['end']) : '',
                );

                if ($values['name'] === '') {
                    $this->tpl->setNotification($this->language->__("notification.no_project_name"), 'error');
                } elseif ($values['clientId'] === '') {
                    $this->tpl->setNotification($this->language->__("notification.no_client"), 'error');
                } else {
                    $projectName = $values['name'];
                    $id = $this->projectRepo->addProject($values);
                    $this->projectService->changeCurrentSessionProject($id);

                    $users = $this->projectRepo->getUsersAssignedToProject($id);

                    $mailer->setContext('project_created');
                    $mailer->setSubject($this->language->__('email_notifications.project_created_subject'));
                    $actual_link = BASE_URL . "/projects/showProject/" . $id . "";
                    $message = sprintf($this->language->__('email_notifications.project_created_message'), $actual_link, $id, $projectName, $_SESSION["userdata"]["name"]);
                    $mailer->setHtml($message);

                    $to = array();

                    foreach ($users as $user) {
                        if ($user["notifications"] != 0) {
                            $to[] = $user["username"];
                        }
                    }

                    //$mailer->sendMail($to, $_SESSION["userdata"]["name"]);
                    // NEW Queuing messaging system
                    $this->queueRepo->queueMessageToUsers($to, $message, $this->language->__('email_notifications.project_created_subject'), $id);


                    //Take the old value to avoid nl character
                    $values['details'] = $_POST['details'];

                    $this->tpl->setNotification(sprintf($this->language->__('notifications.project_created_successfully'), BASE_URL . '/leancanvas/simpleCanvas/'), 'success', "project_created");

                    $this->tpl->redirect(BASE_URL . "/projects/showProject/" . $id);
                }


                $this->tpl->assign('project', $values);
            }

            $this->tpl->assign('menuTypes', $this->menuRepo->getMenuTypes());
            $this->tpl->assign('project', $values);
            $this->tpl->assign('availableUsers', $this->userRepo->getAll());
            $this->tpl->assign('clients', $this->clientsRepo->getAll());
            $this->tpl->assign('projectTypes', $this->projectService->getProjectTypes());

            $this->tpl->assign('info', $msgKey);

            $this->tpl->display('projects.newProject');
        }
    }

}
