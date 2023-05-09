<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class users extends controller
    {
        private services\users $usersService;
        private repositories\files $filesRepository;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init()
        {

            $this->usersService = new services\users();
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
                //var_dump("asdf");

                $return = $this->usersService->getProfilePicture($params["profileImage"]);

                if (is_string($return)) {

                    $file = new core\fileupload();
                    $file->displayImageFile($return);

                } else if(is_object($return)){

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
