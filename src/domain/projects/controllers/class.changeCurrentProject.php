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
         * run - display template and edit data
         *
         * @access public
         */
        /**
         * get - handle get requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function get($params)
        {
            if(isset($params['id'])) {

                $id = (int) $params['id'];

                if($this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $id)) {

                    $project = $this->projectService->getProject($id);

                    if($project !== false){

                        $this->projectService->changeCurrentSessionProject($id);

                        $this->tpl->redirect("/dashboard/show");

                    }else{
                        $this->tpl->redirect("/404/");
                    }
                }else{
                    $this->tpl->redirect("/404/");
                }



            }else{

                //$this->tpl->redirect("/404/");

            }

        }


    }

}


