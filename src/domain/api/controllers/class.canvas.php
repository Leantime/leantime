<?php
/**
 * canvas class - Generic canvas API controller
 */
namespace leantime\domain\controllers\api {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class canvas
    {

        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';

        private $tpl;
        private $projects;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projects = new repositories\projects();
            $canvasRepoName = "leantime\\domain\\repositories\\".static::CANVAS_NAME.'canvas';
            $this->canvasRepo = new $canvasRepoName();

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
            $results = $this->canvasRepo->patchCanvasItem($params['id'], $params);

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
