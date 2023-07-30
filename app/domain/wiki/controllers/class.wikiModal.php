<?php

namespace leantime\domain\controllers {

    use leantime\core\controller;
    use leantime\domain\models\wiki;
    use leantime\domain\services;

    class wikiModal extends controller
    {

        private services\wiki $wikiService;

        public function init(services\wiki $wikiService)
        {
            $this->wikiService = $wikiService;
        }

        public function get($params)
        {
            $wiki = app()->make(wiki::class);

            if (isset($_GET["id"])) {
                $wiki = $this->wikiService->getWiki($_GET["id"]);
            }

            $this->tpl->assign("wiki", $wiki);
            $this->tpl->displayPartial("wiki.wikiDialog");
        }

        public function post($params)
        {
            $wiki = app()->make(wiki::class);

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
