<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class changeCurrentProject extends controller
    {

        public function init()
        {

            $this->projectService = new services\projects();
            $this->settingService = new services\setting();

        }

        /**
         * get - handle get requests
         *
         * @access public
         */
        public function get($params)
        {
            if(isset($params['id'])) {

                $id = filter_var($params['id'], FILTER_SANITIZE_NUMBER_INT);

                if($this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $id)) {

                    $project = $this->projectService->getProject($id);

                    if($project !== false){

                        $this->projectService->changeCurrentSessionProject($id);

                        $this->tpl->redirect(BASE_URL."/dashboard/show");

                    }else{
                        $this->tpl->redirect(BASE_URL."/errors/error404");
                    }
                }else{
                    $this->tpl->redirect(BASE_URL."/errors/error404");
                }

            }else{

                $this->tpl->redirect(BASE_URL."/errors/error404");

            }

        }



        /**
         * post - handle post requests (via login for example) and redirects to get
         *
         * @access public
         */
        public function post($params)
        {
            if(isset($_GET['id'])) {

                $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                $this->tpl->redirect(BASE_URL."/projects/changeCurrentProject/".$id);

            }

        }


    }

}


