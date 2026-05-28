<?php

namespace Leantime\Domain\Wiki\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;
use Symfony\Component\HttpFoundation\Response;

class DelWiki extends Controller
{
    private WikiRepository $wikiRepo;

    /**
     * Initializes dependencies.
     */
    public function init(WikiRepository $wikiRepo): void
    {
        $this->wikiRepo = $wikiRepo;
    }

    /**
     * Displays the delete wiki confirmation.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        return $this->tpl->displayPartial('wiki.delWiki');
    }

    /**
     * Handles wiki deletion.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            $this->wikiRepo->delWiki($id);

            $this->tpl->setNotification($this->language->__('notification.wiki_deleted'), 'success', 'wiki_deleted');

            session()->forget('lastArticle');
            session()->forget('currentWiki');

            return Frontcontroller::redirect(BASE_URL.'/wiki/show');
        }

        return $this->tpl->displayPartial('wiki.delWiki');
    }
}
