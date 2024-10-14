<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Wiki\Models\Wiki;
    use Leantime\Domain\Wiki\Services\Wiki as WikiService;
    use Symfony\Component\HttpFoundation\Response;

    class WikiModal extends Controller
    {
        private WikiService $wikiService;

        public function init(WikiService $wikiService): void
        {
            $this->wikiService = $wikiService;
        }

        /**
         * @throws BindingResolutionException
         */
        public function get($params): Response
        {
            $wiki = app()->make(Wiki::class);

            if (isset($_GET['id'])) {
                $wiki = $this->wikiService->getWiki($_GET['id']);
            }

            $this->tpl->assign('wiki', $wiki);

            return $this->tpl->displayPartial('wiki::partials.wikiDialog');
        }

        /**
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {
            $wiki = app()->make(Wiki::class);

            if (isset($params['id'])) {
                $id = (int) $params['id'];
                //Update
                $wiki->title = $params['title'];
                $this->wikiService->updateWiki($wiki, $id);
                $this->tpl->setNotification('notification.wiki_updated_successfully', 'success', 'wiki_updated');

            } else {
                //New
                $wiki->title = $params['title'];
                $wiki->projectId = session('currentProject');
                $wiki->author = session('userdata.id');

                $id = $this->wikiService->createWiki($wiki);

                //session(["currentWiki" => $id]);

                if ($id) {
                    $this->tpl->setNotification('notification.wiki_created_successfully', 'success', 'wiki_created');
                }

            }

            $this->tpl->closeModal();
            $this->tpl->htmxRefresh();

            return $this->tpl->emptyResponse();
        }
    }

}
