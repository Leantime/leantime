<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Wiki\Models\Article;
    use Leantime\Domain\Wiki\Services\Wiki as WikiService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class ArticleDialog extends Controller
    {
        private WikiService $wikiService;
        private TicketService $ticketService;

        /**
         * @param WikiService   $wikiService
         * @param TicketService $ticketService
         * @return void
         */
        public function init(WikiService $wikiService, TicketService $ticketService): void
        {
            $this->wikiService = $wikiService;
            $this->ticketService = $ticketService;
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function get($params): Response
        {

            $article = app()->make(Article::class);
            $article->data = "far fa-file-alt";

            if (isset($params['id'])) {
                $article = $this->wikiService->getArticle($params['id'], session("currentProject"));
            }

            //Delete milestone relationship
            if (isset($params['removeMilestone']) === true) {
                $article->milestoneId = "";
                $results = $this->wikiService->updateArticle($article);

                if ($results) {
                    $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), "success", "articlemilestone_unlinked");
                    return Frontcontroller::redirect(BASE_URL . "/wiki/articleDialog/" . $article->id);
                }
            }

            if (session("currentWiki") != '') {
                $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(session("currentWiki"), session("userdata.id"));
            } else {
                $wikiHeadlines = array();
            }

            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => session("currentProject")]);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign("wikiHeadlines", $wikiHeadlines);
            $this->tpl->assign("article", $article);
            return $this->tpl->displayPartial("wiki.articleDialog");
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {

            $article = app()->make(Article::class);

            if (isset($_GET["id"])) {
                $id = $_GET["id"];
                $article = $this->wikiService->getArticle($id, session("currentProject"));

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
                    $params['editFrom'] =  dtHelper()->userNow()->formatDateForUser();
                    $params['editTo'] =  dtHelper()->userNow()->addDays(7)->formatDateForUser();
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
                    $this->tpl->setNotification("notification.article_updated_successfully", "success", "article_updated");
                }
            } else {
                //New
                $article->title = $params['title'];
                $article->author = session("userdata.id");
                $article->canvasId = session("currentWiki");
                $article->data = $params['articleIcon'];
                $article->tags = $params['tags'];
                $article->status = $params['status'];
                $article->parent = $params['parent'];
                $article->description = $params['description'];

                $id = $this->wikiService->createArticle($article);

                if ($id) {
                    $this->tpl->setNotification("notification.article_created_successfully", "success", "article_created");
                }
            }
            if (isset($params["saveAndCloseArticle"]) === true && $params["saveAndCloseArticle"] == 1) {
                return Frontcontroller::redirect(BASE_URL . "/wiki/articleDialog/" . $id . "?closeModal=1");
            } else {
                return Frontcontroller::redirect(BASE_URL . "/wiki/articleDialog/" . $id);
            }
        }
    }
}
