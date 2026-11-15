<?php

namespace Leantime\Domain\Wiki\Controllers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Wiki\Permissions\WikiPermissions;
use Leantime\Domain\Wiki\Services\Wiki as WikiService;
use Symfony\Component\HttpFoundation\Response;

class DelArticle extends Controller
{
    private WikiService $wikiService;

    private PermissionService $permissionService;

    /**
     * Initializes dependencies.
     */
    public function init(WikiService $wikiService, PermissionService $permissionService): void
    {
        $this->wikiService = $wikiService;
        $this->permissionService = $permissionService;
    }

    /**
     * Displays the delete article confirmation.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(WikiPermissions::DELETE, entityScoped: true)]
    public function get(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        // The Docs screens offer Delete by the article's own project; authorize the confirmation there.
        $articleProjectId = $id > 0 ? $this->wikiService->getArticleProjectId($id) : null;
        if ($articleProjectId === null) {
            return $this->tpl->displayPartial('errors.error404', responseCode: 404);
        }

        $this->permissionService->authorize(WikiPermissions::DELETE, $articleProjectId);

        return $this->tpl->displayPartial('wiki.delArticle');
    }

    /**
     * Handles article deletion. The controller gate defers (entityScoped) to the service's
     * deleteArticle(), which authorizes DELETE against the article's REAL project.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(WikiPermissions::DELETE, entityScoped: true)]
    public function post(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            $this->wikiService->deleteArticle($id);

            $this->tpl->setNotification($this->language->__('notification.article_deleted'), 'success', 'article_deleted');

            return Frontcontroller::redirect(BASE_URL.'/wiki/show');
        }

        return $this->tpl->displayPartial('wiki.delArticle');
    }
}
