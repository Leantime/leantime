<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Fileupload as FileuploadCore;
use Leantime\Core\Controller;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
class Projects extends Controller
    {
        private FileuploadCore $fileUpload;
        private ProjectService $projectService;
        private FileRepository $filesRepository;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(
            FileuploadCore $fileUpload,
            ProjectService $projectService,
            FileRepository $filesRepository
        ) {
            $this->fileUpload = $fileUpload;
            $this->projectService = $projectService;
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

            if (isset($params["projectAvatar"])) {
                $return = $this->projectService->getProjectAvatar($params["projectAvatar"]);

                if (is_array($return)) {
                    $file = $this->fileUpload;
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
                $_FILES['file']['name'] = "profileImage-" . $_SESSION['currentProject'] . ".png";

                $this->projectService->setProjectAvatar($_FILES, $_SESSION['currentProject']);

                $_SESSION['msg'] = "PICTURE_CHANGED";
                $_SESSION['msgT'] = "success";

                echo "{status:ok}";
            }

            if (isset($params['action']) && $params['action'] == "sortIndex" && isset($params["payload"]) === true) {
                $handler = null;
                if (isset($params["handler"]) == true) {
                    $handler = $params["handler"];
                }

                $results = $this->projectService->updateProjectStatusAndSorting($params["payload"], $handler);

                if ($results === true) {
                    echo "{status:ok}";
                } else {
                    echo "{status:failure}";
                }
            }

            if (isset($params['action']) && $params['action'] == "ganttSort") {
                $results = $this->projectService->updateProjectSorting($params["payload"]);

                if ($results === true) {
                    echo "{status:ok}";
                } else {
                    echo "{status:failure}";
                }
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

            if (isset($params['id'])) {
                $results = $this->projectService->patch($params['id'], $params);
            }

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

            if (isset($params['patchProjectProgress'])) {
                if ($this->projectService->updateProjectProgress($params['values'], $_SESSION['currentProject'])) {
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
