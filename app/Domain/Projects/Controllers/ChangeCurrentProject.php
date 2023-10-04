<?php

namespace Leantime\Domain\Projects\Controllers {

    use JetBrains\PhpStorm\NoReturn;
    use Leantime\Core\Controller;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Setting\Services\Setting as SettingService;

    /**
     *
     */
    class ChangeCurrentProject extends Controller
    {
        private ProjectService $projectService;
        private SettingService $settingService;

        /**
         * @param ProjectService $projectService
         * @param SettingService $settingService
         * @return void
         */
        public function init(ProjectService $projectService, SettingService $settingService): void
        {
            $this->projectService = $projectService;
            $this->settingService = $settingService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         */
        #[NoReturn] public function get($params)
        {
            if (isset($params['id'])) {
                $id = filter_var($params['id'], FILTER_SANITIZE_NUMBER_INT);

                if ($this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $id)) {
                    $project = $this->projectService->getProject($id);

                    if ($project !== false) {
                        $this->projectService->changeCurrentSessionProject($id);

                        $defaultURL = "/dashboard/show";
                        $redirectFilter = static::dispatch_filter("defaultProjectUrl", $defaultURL, $project);

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
