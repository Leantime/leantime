<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Users\Services\Users as UserService;

    /**
     *
     */

    /**
     *
     */
    class Files extends Controller
    {
        private UserService $usersService;
        private FileRepository $fileRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(FileRepository $fileRepo, UserService $usersService)
        {
            $this->usersService = $usersService;
            $this->fileRepo = $fileRepo;
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {

            //FileUpload
            if (isset($_FILES['file']) && isset($_GET['module']) && isset($_GET['moduleId'])) {
                $module = htmlentities($_GET['module']);
                $id = (int) $_GET['moduleId'];
                echo json_encode($this->fileRepo->upload($_FILES, $module, $id));
                return;
            }

            if (isset($_FILES['file'])) {
                $_FILES['file']['name'] = "pastedImage.png";

                $file = $this->fileRepo->upload($_FILES, 'project', $_SESSION['currentProject']);

                echo BASE_URL . "/download.php?module=private&encName=" . $file['encName'] . "&ext=" . $file['extension'] . "&realName=" . $file['realName'] . "";
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
