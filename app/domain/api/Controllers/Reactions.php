<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Reactions\Services\Reactions as ReactionService;
class Reactions extends Controller
    {
        private ReactionService $reactionsService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(ReactionService $reactionsService)
        {
            $this->reactionsService = $reactionsService;
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
            if ($params["action"] == "add") {
                return $this->reactionsService->addReaction($_SESSION['userdata']['id'], $params['module'], $params['moduleId'], $params['reaction']);
            }

            if ($params["action"] == "remove") {
                return $this->reactionsService->removeReaction($_SESSION['userdata']['id'], $params['module'], $params['moduleId'], $params['reaction']);
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
