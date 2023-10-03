<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Ideation\Repositories\Ideation as IdeationRepository;
    use Leantime\Core\Controller;

    /**
     *
     */

    /**
     *
     */
    class Ideation extends Controller
    {
        private ProjectRepository $projects;
        private IdeationRepository $ideationAPIRepo;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(ProjectRepository $projects, IdeationRepository $ideationAPIRepo)
        {
            $this->projects = $projects;
            $this->ideationAPIRepo = $ideationAPIRepo;
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

            if (isset($params['action']) && $params['action'] == "ideationSort" && isset($params["payload"]) === true) {
                $sortOrder = $params["payload"];

                $results = $this->ideationAPIRepo->updateIdeationSorting($sortOrder);

                if ($results === true) {
                    echo "{status:ok}";
                } else {
                    echo "{status:failure}";
                }
            } elseif (isset($params['action']) && $params['action'] == "statusUpdate" && isset($params["payload"]) === true) {
                $results = $this->ideationAPIRepo->bulkUpdateIdeationStatus($params["payload"]);

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
            $results = $this->ideationAPIRepo->patchCanvasItem($params['id'], $params);

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
