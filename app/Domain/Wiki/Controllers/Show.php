<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Wiki\Models\Wiki;
    use Leantime\Domain\Wiki\Services\Wiki as WikiService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Show extends Controller
    {
        private WikiService $wikiService;
        private CommentService $commentService;

        /**
         * @param WikiService    $wikiService
         * @param CommentService $commentService
         * @return void
         */
        public function init(WikiService $wikiService, CommentService $commentService): void
        {
            $this->wikiService = $wikiService;
            $this->commentService = $commentService;
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function get($params): Response
        {

            $currentArticle = '';
            $wikiHeadlines = array();

            //Get all project wikis, creates one if none exists
            $wikis = $this->wikiService->getAllProjectWikis(session("currentProject"));

            //Special case: Setting wiki (active action), set wiki, headlines and current Article
            if (isset($_GET['setWiki'])) {

                $wikiId = (int)$_GET['setWiki'];

                return $this->setWikiAndRedirect($wikiId);

            }


            if(isset($params['id'])) {

                $currentArticle = $this->wikiService->setCurrentArticle($params['id'], session('usersettings.id'));

                if($currentArticle == false) {
                    $this->wikiService->clearWikiCache();
                    return Frontcontroller::redirect(BASE_URL."/errors/error404");
                }

            }else if (
                session()->exists("lastArticle") &&
                session("lastArticle") != '' &&
                ! isset($params['id'])) {

                $currentArticle = $this->wikiService->setCurrentArticle(session("lastArticle"), session('usersettings.id'));

                if($currentArticle) {
                    return Frontcontroller::redirect(BASE_URL . "/wiki/show/" . $currentArticle->id);
                }

            //If neither session is set nor the params id we are coming in fresh. Grab the article from wiki if there is one
            }else{

                //False is okay, just an empty wiki
                $success = $this->wikiService->setCurrentWiki(session("currentWiki"));
                if ($success === false) {

                    //Try getting the first wiki
                    $success = $this->wikiService->setCurrentWiki($wikis[0]->id);

                    if ($success === false) {
                        $this->wikiService->clearWikiCache();
                        return Frontcontroller::redirect(BASE_URL."/errors/error404");
                    }
                }

                $defaultArticle = $this->wikiService->getDefaultArticleForWiki(session("currentWiki"), session('userdata.id'));
                if ($defaultArticle !== false) {
                    session(["lastArticle" => $defaultArticle->id]);
                    return Frontcontroller::redirect(BASE_URL . "/wiki/show/" . $defaultArticle->id);
                }
                //If not it's really just empty.

            }

            //At this point we should have a currentWiki. Even if non exist
            $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(session("currentWiki"), session("userdata.id"));
            if(!$wikiHeadlines) {
                $wikiHeadlines = array();
            }

            //Get the actual wiki content
            $currentWiki = $this->wikiService->getWiki(session("currentWiki"));
            if(empty($currentWiki)) {
                $this->wikiService->clearWikiCache();
                //If we can't find a current wiki at this point something went wrong
                return Frontcontroller::redirect(BASE_URL."/errors/error404");
            }


            //Delete comment
            if (isset($_GET['delComment']) === true) {
                $commentId = (int)($_GET['delComment']);

                $this->commentService->deleteComment($commentId);

                $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success", "wikicomment_deleted");
            }

            if (isset($currentArticle->id)) {
                $comment = $this->commentService->getComments('article', $currentArticle->id, 0);
            } else {
                $comment = array();
            }


            $this->tpl->assign('comments', $comment);
            $this->tpl->assign('numComments', count($comment));
            $this->tpl->assign('currentArticle', $currentArticle);
            $this->tpl->assign('currentWiki', $currentWiki);
            $this->tpl->assign('wikis', $wikis);
            $this->tpl->assign('wikiHeadlines', $wikiHeadlines);

            return $this->tpl->display("wiki.show");
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {

            if (isset($_GET['id']) === true) {
                $id = (int)($_GET['id']);
                $currentArticle = $this->wikiService->getArticle($id, session("currentProject"));

                if (isset($_POST['comment']) === true) {
                    if ($this->commentService->addComment($_POST, "article", $id, $currentArticle)) {
                        $this->tpl->setNotification(
                            $this->language->__("notifications.comment_create_success"),
                            "success",
                            "wikicomment_created"
                        );
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                    }
                }

                return Frontcontroller::redirect(BASE_URL . "/wiki/show/" . $id);
            }

            return Frontcontroller::redirect(BASE_URL . "/wiki/show/");
        }

        protected function setWikiAndRedirect($id) {

            $this->wikiService->clearWikiCache();

            $success = $this->wikiService->setCurrentWiki($id);

            if ($success === false) {
                $this->wikiService->clearWikiCache();
                return Frontcontroller::redirect(BASE_URL."/errors/error404");
            }

            $defaultArticle = $this->wikiService->getDefaultArticleForWiki($id, session('userdata.id'));
            if ($defaultArticle !== false) {
                session(["lastArticle" => $defaultArticle->id]);
                return Frontcontroller::redirect(BASE_URL . "/wiki/show/" . $defaultArticle->id);
            }

            return Frontcontroller::redirect(BASE_URL . "/wiki/show/");

        }
    }

}
