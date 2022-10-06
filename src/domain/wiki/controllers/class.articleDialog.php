<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\models\wiki;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class articleDialog {


        public function __construct() {

            $this->tpl = new core\template();
            $this->wikiService = new services\wiki();
            $this->ticketService = new services\tickets();
        }

        public function get($params) {

            $article = new wiki\article();
            $article->data = "far fa-file-alt";

            if(isset($params['id'])){
                $article = $this->wikiService->getArticle($params['id'], $_SESSION['currentProject']);
            }

            //Delete milestone relationship
            if (isset($params['removeMilestone']) === true) {
                $milestoneId = (int)($params['removeMilestone']);

                $this->articleService->patchArticle($params['id'], array("milestoneId" => ''));

                $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), "success");
            }

            if($_SESSION['currentWiki'] != '') {
                $wikiHeadlines = $this->wikiService->getAllWikiHeadlines($_SESSION['currentWiki'], $_SESSION['userdata']['id']);
            }else{
                $wikiHeadlines = array();
            }

            $this->tpl->assign("milestones",  $this->ticketService->getAllMilestones($_SESSION["currentProject"]));
            $this->tpl->assign("wikiHeadlines", $wikiHeadlines);
            $this->tpl->assign("article", $article);
            $this->tpl->displayPartial("wiki.articleDialog");


        }

        public function post($params) {

            $article = new wiki\article();

            if(isset($_GET["id"])){

                $id = $_GET["id"];
                $article = $this->wikiService->getArticle($id, $_SESSION['currentProject']);

                $article->title = $params['title'];

                $article->data = $params['articleIcon'];
                $article->tags = $params['tags'];
                $article->status = $params['status'];
                $article->parent = $params['parent'];
                $article->description = $params['description'];
                $article->milestoneId = $params['milestoneId'];

                if (isset($params['newMilestone']) && $params['newMilestone'] != '') {

                    $params['headline'] = $params['newMilestone'];
                    $params['tags'] = "#ccc";
                    $params['editFrom'] = date("Y-m-d");
                    $params['editTo'] = date("Y-m-d", strtotime("+1 week"));
                    $milestoneId = $this->ticketService->quickAddMilestone($params);
                    if ($milestoneId !== false) {
                        $article->milestoneId = $milestoneId;
                    }
                }

                if (isset($params['existingMilestone']) && $params['existingMilestone'] != '') {
                    $article->milestoneId = $params['existingMilestone'];
                }

                $results = $this->wikiService->updateArticle($article);

                if($results){
                    $this->tpl->setNotification("notification.article_updated_successfully", "success");
                }

                if(isset($params["saveAndCloseArticle"]) === true && $params["saveAndCloseArticle"] == 1) {
                    $this->tpl->redirect(BASE_URL."/wiki/articleDialog/".$id."?closeModal=1");
                }else {
                    $this->tpl->redirect(BASE_URL."/wiki/articleDialog/".$id);
                }

            }else{
                //New
                $article->title = $params['title'];
                $article->author = $_SESSION['userdata']['id'];
                $article->canvasId = $_SESSION['currentWiki'];
                $article->data = $params['articleIcon'];
                $article->tags = $params['tags'];
                $article->status = $params['status'];
                $article->parent = $params['parent'];
                $article->description = $params['description'];

                $id = $this->wikiService->createArticle($article);

                if($id){
                    $this->tpl->setNotification("notification.article_created_successfully", "success");
                }

                if(isset($params["saveAndCloseArticle"]) === true && $params["saveAndCloseArticle"] == 1) {
                    $this->tpl->redirect(BASE_URL."/wiki/articleDialog/".$id."?closeModal=1");
                }else {
                    $this->tpl->redirect(BASE_URL."/wiki/articleDialog/".$id);
                }



            }

        }

    }

}


