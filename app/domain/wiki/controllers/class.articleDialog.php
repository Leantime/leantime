<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\models\wiki;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class articleDialog extends controller
    {
        private services\wiki $wikiService;
        private services\tickets $ticketService;

        public function init(services\wiki $wikiService, services\tickets $ticketService)
        {
            $this->wikiService = $wikiService;
            $this->ticketService = $ticketService;
        }

        public function get($params)
        {

            $article = app()->make(wiki\article::class);
            $article->data = "far fa-file-alt";

            if (isset($params['id'])) {
                $article = $this->wikiService->getArticle($params['id'], $_SESSION['currentProject']);
            }

            //Delete milestone relationship
            if (isset($params['removeMilestone']) === true) {
                $article->milestoneId = "";
                $results = $this->wikiService->updateArticle($article);

                if ($results) {
                    $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), "success");
                    $this->tpl->redirect(BASE_URL . "/wiki/articleDialog/" . $article->id);
                }
            }

            if ($_SESSION['currentWiki'] != '') {
                $wikiHeadlines = $this->wikiService->getAllWikiHeadlines($_SESSION['currentWiki'], $_SESSION['userdata']['id']);
            } else {
                $wikiHeadlines = array();
            }

            $prepareTicketSearchArray = $this->ticketService->prepareTicketSearchArray(["sprint" => '', "type"=> "milestone"]);
            $allProjectMilestones = $this->ticketService->getAllMilestones($prepareTicketSearchArray);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign("wikiHeadlines", $wikiHeadlines);
            $this->tpl->assign("article", $article);
            $this->tpl->displayPartial("wiki.articleDialog");
        }

        public function post($params)
        {

            $article = app()->make(wiki\article::class);

            if (isset($_GET["id"])) {
                $id = $_GET["id"];
                $article = $this->wikiService->getArticle($id, $_SESSION['currentProject']);

                $article->title = $params['title'];

                $article->data = $params['articleIcon'];
                $article->tags = $params['tags'];
                $article->status = $params['status'];
                $article->parent = $params['parent'];
                $article->description = $params['description'];
                $article->milestoneId = $params['milestoneId'] ?? $article->milestoneId;

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

                if ($results) {
                    $this->tpl->setNotification("notification.article_updated_successfully", "success");
                }

                if (isset($params["saveAndCloseArticle"]) === true && $params["saveAndCloseArticle"] == 1) {
                    $this->tpl->redirect(BASE_URL . "/wiki/articleDialog/" . $id . "?closeModal=1");
                } else {
                    $this->tpl->redirect(BASE_URL . "/wiki/articleDialog/" . $id);
                }
            } else {
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

                if ($id) {
                    $this->tpl->setNotification("notification.article_created_successfully", "success");
                }

                if (isset($params["saveAndCloseArticle"]) === true && $params["saveAndCloseArticle"] == 1) {
                    $this->tpl->redirect(BASE_URL . "/wiki/articleDialog/" . $id . "?closeModal=1");
                } else {
                    $this->tpl->redirect(BASE_URL . "/wiki/articleDialog/" . $id);
                }
            }
        }
    }

}
