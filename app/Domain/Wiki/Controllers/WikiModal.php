<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Wiki\Models\Wiki;
    use Leantime\Domain\Wiki\Services\Wiki as WikiService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class WikiModal extends Controller
    {
        private WikiService $wikiService;

        /**
         * @param WikiService $wikiService
         * @return void
         */
        public function init(WikiService $wikiService): void
        {
            $this->wikiService = $wikiService;
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function get($params): Response
        {
            $wiki = app()->make(Wiki::class);

            if (isset($_GET["id"])) {
                $wiki = $this->wikiService->getWiki($_GET["id"]);
            }

            $this->tpl->assign("wiki", $wiki);
            return $this->tpl->displayPartial("wiki.wikiDialog");
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {
            $wiki = app()->make(Wiki::class);

            if (isset($_GET["id"])) {
                $id = (int) $_GET["id"];
                //Update
                $wiki->title = $params['title'];
                $this->wikiService->updateWiki($wiki, $id);
                $this->tpl->setNotification("notification.wiki_updated_successfully", "success", "wiki_updated");
                return Frontcontroller::redirect(BASE_URL . "/wiki/wikiModal/" . $id);

            } else {
                //New
                $wiki->title = $params['title'];
                $wiki->projectId = session("currentProject");
                $wiki->author = session("userdata.id");

                $id = $this->wikiService->createWiki($wiki);

                //session(["currentWiki" => $id]);

                if ($id) {
                    $this->tpl->setNotification("notification.wiki_created_successfully", "success", "wiki_created");
                    return Frontcontroller::redirect(BASE_URL . "/wiki/wikiModal/" . $id . "?closeModal=1");
                }

                return Frontcontroller::redirect(BASE_URL . "/wiki/wikiModal/" . $id . "");
            }
        }
    }

}
