<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class projects extends controller
    {
        private services\projects $projectService;
        private repositories\files $filesRepository;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init()
        {

            $this->projectService = new services\projects();
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

            if (isset($params["projectAvatar"])) {

                $return = $this->projectService->getProjectAvatar($params["projectAvatar"]);

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
                $_FILES['file']['name'] = "profileImage-". $_SESSION['currentProject'] .".png";

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
            } else {
                echo "{status:failure, message: 'ID not set'}";
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
