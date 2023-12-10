<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeationRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;

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
            if (
                ! isset($params['action'], $params['payload'])
                || ! in_array($params['action'], ['ideationSort', 'statusUpdate'])
            ) {
                return $this->tpl->displayJson(['status' => 'failure'], 400);
            }

            foreach (
                [
                'ideationSort' => fn () => $this->ideationAPIRepo->updateIdeationSorting($params['payload']),
                'statusUpdate' => fn () => $this->ideationAPIRepo->bulkUpdateIdeationStatus($params["payload"]),
                ] as $param => $callback
            ) {
                if ($param !== $params['action']) {
                    continue;
                }

                if (! $callback()) {
                    return $this->tpl->displayJson(['status' => 'failure'], 500);
                }

                break;
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            if (! $this->ideationAPIRepo->patchCanvasItem($params['id'], $params)) {
                return $this->tpl->displayJson(['status', 'failure'], 500);
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
