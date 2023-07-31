<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class files extends controller
    {
        private services\users $usersService;
        private repositories\files $fileRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(repositories\files $fileRepo, services\users $usersService)
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
