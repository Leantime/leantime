<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;

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
                if (! $this->ideaAPIRepo->updateIdeaSorting($params['payload'])) {
                    return $this->tpl->displayJson(['status' => 'failure'], 500);
                }

                return $this->tpl->displayJson(['status' => 'ok']);
            }

            if (isset($params['action']) && $params['action'] == "statusUpdate" && isset($params["payload"]) === true) {
                if (! $this->ideaAPIRepo->bulkUpdateIdeaStatus($params["payload"])) {
                    return $this->tpl->displayJson(['status' => 'failure'], 500);
                }

                return $this->tpl->displayJson(['status' => 'ok']);
            }

            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            if (
                ! isset($params['id'])
                || ! $this->ideaAPIRepo->patchCanvasItem($params['id'], $params)) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
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
