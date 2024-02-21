<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Reactions\Services\Reactions as ReactionService;

    /**
     *
     */
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

                if (! $this->reactionsService->addReaction($_SESSION['userdata']['id'], $params['module'], $params['moduleId'], $params['reaction'])) {
                    return $this->tpl->displayJson(['status' => 'failure'], 500);
                }

                return $this->tpl->displayJson(['status' => 'ok']);
            }

            if ($params["action"] == "remove") {
                if (! $this->reactionsService->removeReaction($_SESSION['userdata']['id'], $params['module'], $params['moduleId'], $params['reaction'])) {
                    return $this->tpl->displayJson(['status' => 'failure'], 500);
                }

                return $this->tpl->displayJson(['status' => 'ok']);
            }

            return $this->tpl->displayJson(['error' => 'Bad Request'], 400);
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
