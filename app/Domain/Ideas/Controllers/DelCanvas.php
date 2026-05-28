<?php

namespace Leantime\Domain\Ideas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Symfony\Component\HttpFoundation\Response;

class DelCanvas extends Controller
{
    private IdeaRepository $ideaRepo;

    /**
     * Initializes dependencies.
     */
    public function init(IdeaRepository $ideaRepo): void
    {
        $this->ideaRepo = $ideaRepo;
    }

    /**
     * Displays the delete idea board confirmation.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        return $this->tpl->display('ideas.delCanvas');
    }

    /**
     * Handles idea board deletion.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            $this->ideaRepo->deleteCanvas($id);

            session()->forget('currentIdeaCanvas');
            $this->tpl->setNotification($this->language->__('notification.idea_board_deleted'), 'success', 'ideaboard_deleted');

            return Frontcontroller::redirect(BASE_URL.'/ideas/showBoards');
        }

        return $this->tpl->display('ideas.delCanvas');
    }
}
