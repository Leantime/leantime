<?php

namespace Leantime\Domain\Sprints\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Projects\Services\Projects;
    use Leantime\Domain\Sprints\Models\Sprints as SprintModel;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;

    class EditSprint extends Controller
    {
        private SprintService $sprintService;

        private Projects $projectService;

        /**
         * constructor - initialize private variables
         */
        public function init(
            SprintService $sprintService,
            Projects $projectService,
        ) {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->sprintService = $sprintService;
            $this->projectService = $projectService;
        }

        /**
         * get - handle get requests
         */
        public function get($params)
        {
            if (isset($params['id'])) {
                $sprint = $this->sprintService->getSprint((int) $params['id']);
            } else {
                $sprint = app()->make(SprintModel::class);

                $startDate = dtHelper()->userNow();
                $endDate = dtHelper()->userNow()->addDays(13);

                $sprint->startDate = $startDate;
                $sprint->endDate = $endDate;
            }

            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser(userId: session('userdata.id'), projectStatus: 'open', projectTypes: 'project');

            $this->tpl->assign('allAssignedprojects', $allAssignedprojects);
            $this->tpl->assign('sprint', $sprint);

            return $this->tpl->displayPartial('sprints.sprintdialog');
        }

        /**
         * post - handle post requests
         */
        public function post($params)
        {
            // If ID is set its an update

            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser(session('userdata.id'), 'open');
            $this->tpl->assign('allAssignedprojects', $allAssignedprojects);

            if ($params['startDate'] == '' || $params['endDate'] == '') {

                $this->tpl->setNotification('First day and last day are required', 'error');
                $this->tpl->assign('sprint', (object) $params);

                return $this->tpl->displayPartial('sprints.sprintdialog');
            }

            if (isset($_GET['id']) && $_GET['id'] > 0) {
                $params['id'] = (int) $_GET['id'];
                $sprintId = $params['id'];
                if ($this->sprintService->editSprint($params)) {
                    $this->tpl->setNotification('Sprint edited successfully', 'success');
                } else {
                    $this->tpl->setNotification('There was a problem saving the sprint', 'error');
                }
            } else {
                if ($sprintId = $this->sprintService->addSprint($params)) {
                    $this->tpl->setNotification('Sprint created successfully.', 'success');
                } else {
                    $this->tpl->setNotification('There was a problem saving the sprint', 'error');
                }
            }

            $sprint = $this->sprintService->getSprint($sprintId);
            $this->tpl->assign('sprint', $sprint);

            return $this->tpl->displayPartial('sprints.sprintdialog');
        }

        /**
         * put - handle put requests
         */
        public function put($params) {}

        /**
         * delete - handle delete requests
         */
        public function delete($params) {}
    }
}
