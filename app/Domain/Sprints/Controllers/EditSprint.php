<?php

namespace Leantime\Domain\Sprints\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Sprints\Permissions\SprintsPermissions;
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
        $this->sprintService = $sprintService;
        $this->projectService = $projectService;
    }

    /**
     * get - handle get requests
     */
    #[RequiresPermission(SprintsPermissions::VIEW)]
    public function get($params)
    {
        if (isset($params['id'])) {
            $sprint = $this->sprintService->getSprint((int) $params['id']);
            $contextProjectId = $sprint ? (int) $sprint->projectId : null;
        } else {
            $sprint = $this->sprintService->getNewSprint();
            // Default the create context to the current project. assignProjectChoices() only
            // locks the picker when that project is a program, so creating a sprint from a
            // program's task-screen pageheader makes a PROGRAM sprint, while normal project
            // boards keep the full project picker unchanged.
            $contextProjectId = isset($params['projectId']) ? (int) $params['projectId'] : (int) session('currentProject');
        }

        $this->assignProjectChoices($contextProjectId);
        $this->tpl->assign('sprint', $sprint);

        return $this->tpl->displayPartial('sprints.sprintdialog');
    }

    /**
     * Assign the project picker choices for the sprint dialog. When the sprint belongs to a
     * program (PgmPro), offer only that program as a locked option so a program sprint can't
     * be reassigned to one of its child projects.
     */
    private function assignProjectChoices(?int $contextProjectId): void
    {
        $allAssignedprojects = $this->projectService->getProjectsAssignedToUser(
            userId: session('userdata.id'),
            projectStatus: 'open',
            projectTypes: 'project'
        );

        $lockProject = false;

        if ($contextProjectId) {
            $project = $this->projectService->getProject($contextProjectId);
            if (is_array($project) && ($project['type'] ?? '') === 'program') {
                $allAssignedprojects = [[
                    'id' => $project['id'],
                    'name' => $project['name'],
                ]];
                $lockProject = true;
            }
        }

        $this->tpl->assign('allAssignedprojects', $allAssignedprojects);
        $this->tpl->assign('lockProject', $lockProject);
    }

    /**
     * post - handle post requests
     *
     * Handles both create (no id) and edit (id present), so the controller gate defers
     * (entityScoped): the service's addSprint/editSprint each authorize the correct verb
     * (CREATE vs EDIT) against the correct per-entity project, which is the authoritative gate.
     */
    #[RequiresPermission(SprintsPermissions::EDIT, entityScoped: true)]
    public function post($params)
    {
        // If ID is set its an update

        $submittedValues = $params;
        $contextProjectId = isset($params['projectId']) ? (int) $params['projectId'] : null;

        try {
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
        } catch (MissingParameterException $e) {
            $this->tpl->setNotification($e->getMessage(), 'error');
            $this->assignProjectChoices($contextProjectId);
            $this->tpl->assign('sprint', (object) $submittedValues);

            return $this->tpl->displayPartial('sprints.sprintdialog');
        }

        $sprint = $this->sprintService->getSprint($sprintId);
        $this->assignProjectChoices($sprint ? (int) $sprint->projectId : $contextProjectId);
        $this->tpl->assign('sprint', $sprint);

        return $this->tpl->displayPartial('sprints.sprintdialog');
    }
}
