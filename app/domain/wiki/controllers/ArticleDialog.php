<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Wiki\Models\Wiki;
    use Leantime\Domain\Wiki\Services\Wiki as WikiService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Auth\Services\Auth;

    class ArticleDialog extends Controller
    {
        private WikiService $wikiService;
        private TicketService $ticketService;

        public function init(WikiService $wikiService, TicketService $ticketService)
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

            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $_SESSION["currentProject"]]);
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
