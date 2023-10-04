<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller;
    use Leantime\Domain\Wiki\Models\Wiki;
    use Leantime\Domain\Wiki\Services\Wiki as WikiService;

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
         * @return void
         * @throws BindingResolutionException
         */
        public function get($params): void
        {
            $wiki = app()->make(Wiki::class);

            if (isset($_GET["id"])) {
                $wiki = $this->wikiService->getWiki($_GET["id"]);
            }

            $this->tpl->assign("wiki", $wiki);
            $this->tpl->displayPartial("wiki.wikiDialog");
        }

        /**
         * @param $params
         * @return void
         * @throws BindingResolutionException
         */
        public function post($params): void
        {
            $wiki = app()->make(Wiki::class);

            if (isset($_GET["id"])) {
                $id = (int) $_GET["id"];
                //Update
                $wiki->title = $params['title'];
                $this->wikiService->updateWiki($wiki, $id);
                $this->tpl->setNotification("notification.wiki_updated_successfully", "success");
                $this->tpl->redirect(BASE_URL . "/wiki/wikiModal/" . $id);
            } else {
            //New
                $wiki->title = $params['title'];
                $wiki->projectId = $_SESSION['currentProject'];
                $wiki->author = $_SESSION['userdata']['id'];

                $id = $this->wikiService->createWiki($wiki);

                //$_SESSION['currentWiki'] = $id;

                if ($id) {
                    $this->tpl->setNotification("notification.wiki_created_successfully", "success");
                    $this->tpl->redirect(BASE_URL . "/wiki/wikiModal/" . $id . "?closeModal=1");
                }
            }
        }
    }

}
