<?php

namespace Leantime\Domain\Projects\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Setting\Services\Setting as SettingService;

    class ChangeCurrentProject extends Controller
    {
        private ProjectService $projectService;

        private SettingService $settingService;

        public function init(ProjectService $projectService, SettingService $settingService): void
        {
            $this->projectService = $projectService;
            $this->settingService = $settingService;
        }

        /**
         * get - handle get requests
         */
        public function get($params)
        {
            $id = filter_var($params['id'] ?? '', FILTER_SANITIZE_NUMBER_INT);

            if (
                ! isset($params['id']) ||
                ! $this->projectService->isUserAssignedToProject(session('userdata.id'), $id) ||
                ! $project = $this->projectService->getProject($id)
            ) {
                return Frontcontroller::redirect(BASE_URL.'/errors/error404', 404);
            }

            $this->projectService->changeCurrentSessionProject($id);

            $defaultURL = '/dashboard/show';
            $redirectFilter = self::dispatch_filter('defaultProjectUrl', $defaultURL, $project);

            return Frontcontroller::redirect(BASE_URL.$redirectFilter);
        }

        /**
         * post - handle post requests (via login for example) and redirects to get
         */
        public function post($params)
        {
            if (isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

                return Frontcontroller::redirect(BASE_URL.'/projects/changeCurrentProject/'.$id);
            }
        }
    }

}
