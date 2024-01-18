<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Core\Environment;
    use Leantime\Core\Fileupload as FileuploadCore;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Users extends Controller
    {
        private UserService $usersService;
        private FileRepository $filesRepository;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(
            UserService $usersService,
            FileRepository $filesRepository
        ) {
            $this->usersService = $usersService;
            $this->filesRepository = $filesRepository;
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {

            if (isset($params['assignedProjectUsersAssigned'])) {
            }

            if (isset($params['projectUsersAccess'])) {
                if ($params['projectUsersAccess'] == 'current') {
                    $projectId = $_SESSION['currentProject'];
                } else {
                    $projectId = $params['projectUsersAccess'];
                }

                $users = $this->usersService->getUsersWithProjectAccess($_SESSION['userdata']['id'], $projectId);

                return $this->tpl->displayJson($users);
            }

            if (isset($params["profileImage"])) {
                /**
                 * @var SVG\SVG|array
                 **/
                $svg = $this->usersService->getProfilePicture($params["profileImage"]);

                if (is_array($svg)) {
                    $file = app()->make(FileuploadCore::class);
                    return match ($svg['type']) {
                        'uploaded' => $file->displayImageFile($svg['filename']),
                        'generated' => $file->displayImageFile('avatar', $svg['filename']),
                    };
                }

                $response = new Response($svg->toXMLString());
                $response->headers->set('Content-type', 'image/svg+xml');

                if(app()->make(Environment::class)->debug == false) {
                    $response->headers->set("Pragma", 'public');
                    $response->headers->set("Cache-Control", 'max-age=86400');
                }

                return $response;
            }

            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {
            if (! isset($_FILES['file'])) {
                return $this->tpl->displayJson(['error' => 'File not included'], 400);
            }

            //Updatind User Image
            $_FILES['file']['name'] = "userPicture.png";

            $this->usersService->setProfilePicture($_FILES, $_SESSION['userdata']['id']);

            $_SESSION['msg'] = "PICTURE_CHANGED";
            $_SESSION['msgT'] = "success";

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
                ! in_array(array_keys($params), ['patchModalSettings', 'patchViewSettings', 'patchMenuStateSettings'])
                || (! empty($params['patchModalSettings']) && empty($params['settings']))
                || (! empty($params['patchViewSettings']) && empty($params['value']))
                || (! empty($params['patchMenuStateSettings']) && empty($params['value']))
            ) {
                return $this->tpl->displayJson(['status' => 'failure', 'error' => 'Required params not included in request'], 400);
            }

            $success = false;
            foreach (
                [
                'patchModalSettings' => fn () => $this->usersService->updateUserSettings("modals", $params['settings'], 1),
                'patchViewSettings' => fn () => $this->usersService->updateUserSettings("views", $params['patchViewSettings'], $params['value']),
                'patchMenuStateSettings' => fn () => $this->usersService->updateUserSettings("views", "menuState", $params['value']),
                ] as $param => $callback
            ) {
                if (! isset($params[$param])) {
                    continue;
                }

                $success = $callback();
                break;
            }

            if ($success) {
                return $this->tpl->displayJson(['status' => 'ok']);
            }

            return $this->tpl->displayJson(['status' => 'failure'], 500);
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
