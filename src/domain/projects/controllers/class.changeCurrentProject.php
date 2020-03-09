<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class changeCurrentProject
    {


        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectService = new services\projects();
            $this->settingService = new services\setting();

        }

        /**
         * get - handle get requests
         *
         * @access public
         * @param  paramters or body of the request
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
                        $this->tpl->redirect(BASE_URL."/404/");
                    }
                }else{
                    $this->tpl->redirect(BASE_URL."/404/");
                }

            }else{

                //$this->tpl->redirect(BASE_URL."/404/");

            }

        }



        /**
         * post - handle post requests (via login for example) and redirects to get
         *
         * @access public
         * @param  paramters or body of the request
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


