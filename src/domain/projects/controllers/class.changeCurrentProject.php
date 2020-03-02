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

                $projects = $this->projectService->getProjectIdAssignedToUser($_SESSION['userdata']['id']);

                $isAllowed = false;
                foreach($projects as $item){
                    if($item['projectId'] == $id) {
                        $isAllowed = true;
                        break;
                    }
                }

                if($isAllowed) {

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


