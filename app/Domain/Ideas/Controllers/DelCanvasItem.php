<?php

namespace Leantime\Domain\Ideas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Symfony\Component\HttpFoundation\Response;

class DelCanvasItem extends Controller
{
    private IdeaRepository $ideasRepo;

    /**
     * Initializes dependencies.
     */
    public function init(IdeaRepository $ideasRepo): void
    {
        $this->ideasRepo = $ideasRepo;
    }

    /**
     * Displays the delete idea item confirmation.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        return $this->tpl->displayPartial('ideas.delCanvasItem');
    }

    /**
     * Handles idea item deletion.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            $this->ideasRepo->delCanvasItem($id);

            $this->tpl->setNotification($this->language->__('notification.idea_board_item_deleted'), 'success', 'ideaitem_deleted');

            return Frontcontroller::redirect(BASE_URL.'/ideas/showBoards');
        }

        return $this->tpl->displayPartial('ideas.delCanvasItem');
    }
}
