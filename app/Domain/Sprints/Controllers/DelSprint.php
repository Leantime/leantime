<?php

namespace Leantime\Domain\Sprints\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Sprints\Permissions\SprintsPermissions;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Symfony\Component\HttpFoundation\Response;

class DelSprint extends Controller
{
    private SprintService $sprintService;

    /**
     * Initializes dependencies.
     */
    public function init(SprintService $sprintService): void
    {
        $this->sprintService = $sprintService;
    }

    /**
     * Displays the delete sprint confirmation.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(SprintsPermissions::VIEW)]
    public function get(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);
        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial('sprints.delSprint');
    }

    /**
     * Handles sprint deletion.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(SprintsPermissions::DELETE)]
    public function post(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            $this->sprintService->deleteSprint($id);

            $this->tpl->setNotification($this->language->__('notifications.sprint_deleted_successfully'), 'success');

            if (session()->exists('lastPage')) {
                return Frontcontroller::redirect(session('lastPage'));
            }

            return Frontcontroller::redirect(BASE_URL.'/tickets/showKanban');
        }

        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial('sprints.delSprint');
    }
}
