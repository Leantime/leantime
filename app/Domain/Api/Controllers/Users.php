<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Fileupload as FileuploadCore;
    use Leantime\Core\Controller;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Users\Services\Users as UserService;
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

                $this->tpl->displayJson(json_encode($users));

                return;
            }

            if (isset($params["profileImage"])) {
                $return = $this->usersService->getProfilePicture($params["profileImage"]);

                if (is_array($return)) {
                    $file = app()->make(FileuploadCore::class);
                    if ($return["type"] == "uploaded") {
                        $file->displayImageFile($return["filename"]);
                    } elseif ($return["type"] == "generated") {
                        $file->displayImageFile("avatar", $return["filename"]);
                    }
                } elseif (is_object($return)) {
                    header('Content-type: image/svg+xml');
                    echo $return->toXMLString();
                }
            }
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
            if (isset($_FILES['file'])) {
                $_FILES['file']['name'] = "userPicture.png";

                $this->usersService->setProfilePicture($_FILES, $_SESSION['userdata']['id']);

                $_SESSION['msg'] = "PICTURE_CHANGED";
                $_SESSION['msgT'] = "success";

                echo "{status:ok}";
            }
        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            //Special handling for settings

            if (isset($params['patchModalSettings'])) {
                if ($this->usersService->updateUserSettings("modals", $params['settings'], 1)) {
                    echo "{status:ok}";
                }
            }

            if (isset($params['patchViewSettings'])) {
                if ($this->usersService->updateUserSettings("views", $params['patchViewSettings'], $params['value'])) {
                    echo "{status:ok}";
                }
            }

            if (isset($params['patchMenuStateSettings'])) {
                if ($this->usersService->updateUserSettings("views", "menuState", $params['value'])) {
                    echo "{status:ok}";
                }
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
