<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class users
    {

        private $tpl;
        private $usersService;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->usersService = new services\users;
            $this->filesRepository = new repositories\files();

        }


        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
            if(isset($params["profileImage"])) {

                if($params["profileImage"] == "currentUser") {
                    $return = $this->usersService->getProfilePicture($_SESSION['userdata']['id']);
                    $this->tpl->redirect($return);
                }else{
                    $imageId = (int)$params["profileImage"];
                }

                $file = $this->filesRepository->getFile($imageId);

                $return = BASE_URL.'/images/default-user.png';
                if ($file) {
                    $return = BASE_URL."/download.php?module=" . $file['module'] . "&encName=" . $file['encName'] . "&ext=" . $file['extension'] . "&realName=" . $file['realName'];
                }

                $this->tpl->redirect($return);

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

            if(isset($params['patchModalSettings'])) {

                if($this->usersService->updateUserSettings("modals", $params['settings'], 1)) {
                    echo "{status:ok}";
                }
            }

            if(isset($params['patchViewSettings'])) {

                if($this->usersService->updateUserSettings("views", $params['patchViewSettings'], $params['value'])) {
                    echo "{status:ok}";
                }
            }

            if(isset($params['patchMenuStateSettings'])) {

                if($this->usersService->updateUserSettings("views", "menuState", $params['value'])) {
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
