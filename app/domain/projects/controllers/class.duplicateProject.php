<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class duplicateProject extends controller
    {
        public function init()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager], true);

            $this->projectRepo = new repositories\projects();
            $this->projectService = new services\projects();
            $this->clientRepo = new repositories\clients();
        }

        public function get()
        {

            //Only admins
            if (auth::userIsAtLeast(roles::$manager)) {
                if (isset($_GET['id']) === true) {
                    $id = (int)($_GET['id']);
                    $project = $this->projectService->getProject($id);


                    $this->tpl->assign('allClients', $this->clientRepo->getAll());


                    $this->tpl->assign("project", $project);
                    $this->tpl->displayPartial('projects.duplicateProject');
                } else {
                    $this->tpl->displayPartial('errors.error403');
                }
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }

        public function post($params)
        {

            //Only admins
            if (auth::userIsAtLeast(roles::$manager)) {
                $id = (int)($_GET['id']);
                $projectName = $params['projectName'];
                $startDate = $this->language->getISODateString($params['startDate']);
                $clientId = (int) $params['clientId'];
                $assignSameUsers = false;

                if (isset($params['assignSameUsers'])) {
                    $assignSameUsers = true;
                }

                $result = $this->projectService->duplicateProject($id, $clientId, $projectName, $startDate, $assignSameUsers);

                $this->tpl->setNotification(sprintf($this->language->__("notifications.project_copied_successfully"), BASE_URL . "/projects/changeCurrentProject/" . $result), 'success');

                $this->tpl->redirect(BASE_URL . "/projects/duplicateProject/" . $id);
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }
    }

}
