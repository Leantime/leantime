<?php

namespace Leantime\Domain\Sprints\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Projects\Services\Projects;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Sprints\Models\Sprints as SprintModel;
    use DateTime;
    use DateInterval;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class EditSprint extends Controller
    {
        private SprintService $sprintService;

        private Projects $projectService;

        /**
         * constructor - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            SprintService $sprintService,
            Projects $projectService,
    )
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->sprintService = $sprintService;
            $this->projectService = $projectService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {
            if (isset($params['id'])) {
                $sprint = $this->sprintService->getSprint($params['id']);
            } else {
                $sprint = app()->make(SprintModel::class);
                $startDate = new DateTime();
                $endDate = new DateTime();
                $endDate = $endDate->add(new DateInterval("P13D"));
                $sprint->startDate = $startDate->format($this->language->__("language.dateformat"));
                $sprint->endDate = $endDate->format($this->language->__("language.dateformat"));
            }

            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');

            $this->tpl->assign('allAssignedprojects', $allAssignedprojects);
            $this->tpl->assign('sprint', $sprint);
            return $this->tpl->displayPartial('sprints.sprintdialog');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            //If ID is set its an update

            if ($params['startDate'] == '' || $params['endDate'] == '') {
                $this->tpl->setNotification("First day and last day are required", "error");

                $this->tpl->assign('sprint', (object) $params);
                return $this->tpl->displayPartial('sprints.sprintdialog');
            }

            if (isset($_GET['id']) && $_GET['id'] > 0) {
                $params['id'] = (int)$_GET['id'];

                if ($this->sprintService->editSprint($params)) {
                    $this->tpl->setNotification("Sprint edited successfully", "success");
                } else {
                    $this->tpl->setNotification("There was a problem saving the sprint", "error");
                }
            } else {
                if ($this->sprintService->addSprint($params)) {
                    $this->tpl->setNotification("Sprint created successfully.", "success");
                } else {
                    $this->tpl->setNotification("There was a problem saving the sprint", "error");
                }
            }

            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');

            $this->tpl->assign('allAssignedprojects', $allAssignedprojects);

            $this->tpl->assign('sprint', (object) $params);
            return $this->tpl->displayPartial('sprints.sprintdialog');
        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {
        }
    }
}
