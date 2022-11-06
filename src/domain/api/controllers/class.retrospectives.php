<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class retrospectives extends controller
    {

        private $projects;
        private $retroRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init()
        {

            $this->projects = new repositories\projects();
            $this->retroRepo = new repositories\retrospectives();

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

        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            $results = $this->retroRepo->patchCanvasItem($params['id'], $params);

            if($results === true) {
                echo "{status:ok}";
            }else{
                echo "{status:failure}";
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
