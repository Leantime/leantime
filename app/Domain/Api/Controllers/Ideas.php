<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;

    /**
     *
     */

    /**
     *
     */
    class Ideas extends Controller
    {
        private ProjectRepository $projects;
        private IdeaRepository $ideaAPIRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(ProjectRepository $projects, IdeaRepository $ideaAPIRepo)
        {

            $this->projects = $projects;
            $this->ideaAPIRepo = $ideaAPIRepo;
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

            if (isset($params['action']) && $params['action'] == "ideaSort" && isset($params["payload"]) === true) {
                $sortOrder = $params["payload"];

                $results = $this->ideaAPIRepo->updateIdeaSorting($sortOrder);

                if ($results === true) {
                    echo "{status:ok}";
                } else {
                    echo "{status:failure}";
                }
            } elseif (isset($params['action']) && $params['action'] == "statusUpdate" && isset($params["payload"]) === true) {
                $results = $this->ideaAPIRepo->bulkUpdateIdeaStatus($params["payload"]);

                if ($results === true) {
                    echo "{status:ok}";
                } else {
                    echo "{status:failure}";
                }
            } else {
                echo "{status:failure}";
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
            $results = $this->ideaAPIRepo->patchCanvasItem($params['id'], $params);

            if ($results === true) {
                echo "{status:ok}";
            } else {
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
