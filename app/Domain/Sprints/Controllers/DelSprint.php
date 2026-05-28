<?php

namespace Leantime\Domain\Sprints\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;
use Symfony\Component\HttpFoundation\Response;

class DelSprint extends Controller
{
    private SprintRepository $sprintRepo;

    /**
     * Initializes dependencies.
     */
    public function init(SprintRepository $sprintRepo): void
    {
        $this->sprintRepo = $sprintRepo;
    }

    /**
     * Displays the delete sprint confirmation.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        if (! Auth::userIsAtLeast(Roles::$editor)) {
            return $this->tpl->displayPartial('errors.error403', responseCode: 403);
        }

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);
        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial('sprints.delSprint');
    }

    /**
     * Handles sprint deletion.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        if (! Auth::userIsAtLeast(Roles::$editor)) {
            return $this->tpl->displayPartial('errors.error403', responseCode: 403);
        }

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            $this->sprintRepo->delSprint($id);

            $this->tpl->setNotification($this->language->__('notifications.sprint_deleted_successfully'), 'success');

            session(['currentSprint' => '']);

            if (session()->exists('lastPage')) {
                return Frontcontroller::redirect(session('lastPage'));
            }

            return Frontcontroller::redirect(BASE_URL.'/tickets/showKanban');
        }

        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial('sprints.delSprint');
    }
}
