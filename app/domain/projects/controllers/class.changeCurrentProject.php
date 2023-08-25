<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class changeCurrentProject extends controller
    {
        private services\projects $projectService;
        private services\setting $settingService;

        public function init(services\projects $projectService, services\setting $settingService)
        {
            $this->projectService = $projectService;
            $this->settingService = $settingService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         */
        public function get($params)
        {
            if (isset($params['id'])) {
                $id = filter_var($params['id'], FILTER_SANITIZE_NUMBER_INT);

                if ($this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $id)) {
                    $project = $this->projectService->getProject($id);

                    if ($project !== false) {
                        $this->projectService->changeCurrentSessionProject($id);

                        if (isset($params['redirect'])) {
                            $redirect = strip_tags($params['redirect']);
                            $urlParts = explode("/", $redirect);
                            if (count($urlParts) > 2) {
                                $this->tpl->redirect(BASE_URL . "/" . $urlParts[1] . "/" . $urlParts[2]);
                            }
                        }

                        $defaultURL = "/dashboard/show";
                        $redirectFilter = static::dispatch_filter("defaultProjectUrl", $defaultURL);
                        $this->tpl->redirect(BASE_URL . $redirectFilter);
                    } else {
                        $this->tpl->redirect(BASE_URL . "/errors/error404");
                    }
                } else {
                    $this->tpl->redirect(BASE_URL . "/errors/error404");
                }
            } else {
                $this->tpl->redirect(BASE_URL . "/errors/error404");
            }
        }



        /**
         * post - handle post requests (via login for example) and redirects to get
         *
         * @access public
         */
        public function post($params)
        {
            if (isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                $this->tpl->redirect(BASE_URL . "/projects/changeCurrentProject/" . $id);
            }
        }
    }

}
