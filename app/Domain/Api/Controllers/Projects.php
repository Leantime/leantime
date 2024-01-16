<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Core\Fileupload as FileuploadCore;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Projects extends Controller
    {
        private FileuploadCore $fileUpload;
        private ProjectService $projectService;
        private FileRepository $filesRepository;
        private UserService $usersService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(
            FileuploadCore $fileUpload,
            ProjectService $projectService,
            FileRepository $filesRepository,
            UserService $usersService,
        ) {
            $this->fileUpload = $fileUpload;
            $this->projectService = $projectService;
            $this->filesRepository = $filesRepository;
            $this->usersService = $usersService;
        }


        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
            if (! isset($params['projectAvatar'])) {
                return $this->tpl->displayJson(['status' => 'failure'], 400);
            }

            /**
             * @var SVG\SVG|array
             **/
            $svg = $this->projectService->getProjectAvatar($params["projectAvatar"]);

            if (is_array($svg)) {
                $file = $this->fileUpload;
                return match ($svg['type']) {
                    'uploaded' => $file->displayImageFile($svg['filename']),
                    'generated' => $file->displayImageFile("avatar", $svg['filename']),
                };
            }

            $response = new Response($svg->toXMLString());
            $response->headers->set('Content-type', 'image/svg+xml');
            $response->headers->set("Pragma", 'public');
            $response->headers->set("Cache-Control", 'max-age=86400');

            return $response;
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {
            //Updatind User Image
            if (! empty($_FILES['file'])) {
                $_FILES['file']['name'] = "profileImage-" . $_SESSION['currentProject'] . ".png";

                $this->projectService->setProjectAvatar($_FILES, $_SESSION['currentProject']);

                $_SESSION['msg'] = "PICTURE_CHANGED";
                $_SESSION['msgT'] = "success";

                return $this->tpl->displayJson(['status' => 'ok']);
            }

            if (! isset($params['action'], $params['payload'])) {
                return $this->tpl->displayJson(['status' => 'failure'], 400);
            }

            $callback = match ($params['action']) {
                'sortIndex' => fn () => $this->projectService->updateProjectStatusAndSorting($params["payload"], $handler ?? null),
                'ganttSort' => fn () => $this->projectService->updateProjectSorting($params["payload"]),
            };

            if (! $callback()) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        /**
         * put - Special handling for settings
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            if (
                ! in_array(array_keys($params), ['id', 'patchModalSettings', 'patchViewSettings', 'patchMenuStateSettings', 'patchProjectProgress'])
                || (! empty($params['patchModalSettings']) && empty($params['settings']))
                || (! empty($params['patchViewSettings']) && empty($params['value']))
                || (! empty($params['patchMenuStateSettings']) && empty($params['value']))
                || (! empty($params['patchProjectProgress']) && (empty($params['values']) || empty($_SESSION['currentProject'])))
            ) {
                return $this->tpl->displayJson(['status' => 'failure', 'error' => 'Required params not included in request'], 400);
            }

            foreach (
                [
                'id' => fn () => $this->projectService->patch($params['id'], $params),
                'patchModalSettings' => fn () => $this->usersService->updateUserSettings("modals", $params['settings'], 1),
                'patchViewSettings' => fn () => $this->usersService->updateUserSettings("views", $params['patchViewSettings'], $params['value']),
                'patchMenuStateSettings' => fn () => $this->usersService->updateUserSettings("views", "menuState", $params['value']),
                'patchProjectProgress' => fn () => $this->projectService->updateProjectProgress($params['values'], $_SESSION['currentProject']),
                ] as $param => $callback
            ) {
                if (! isset($params[$param])) {
                    continue;
                }

                if (! $callback()) {
                    return $this->tpl->displayJson(['status' => 'failure', 'error' => 'Something went wrong'], 500);
                }

                return $this->tpl->displayJson(['status' => 'ok']);
            }
        }




        /**
         * delete - handle delete requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function delete($params)
        {
        }
    }

}
