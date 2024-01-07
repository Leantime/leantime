<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller;
    use Leantime\Core\Frontcontroller;
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


            $wikis = $this->wikiService->getAllProjectWikis($_SESSION['currentProject']);
            if (!$wikis || count($wikis) == 0) {
                $wiki = app()->make(Wiki::class);
                $wiki->title = $this->language->__("label.default");
                $wiki->projectId = $_SESSION['currentProject'];
                $wiki->author = $_SESSION['userdata']['id'];

                $id = $this->wikiService->createWiki($wiki);
                $wikis = $this->wikiService->getAllProjectWikis($_SESSION['currentProject']);
            }

            //Option 1: Setting wiki (active action), set wiki, headlines and current Article
            if (isset($_GET['setWiki'])) {
                unset($_SESSION['lastArticle']);
                $wikiId = (int)$_GET['setWiki'];

                $wiki = $this->wikiService->getWiki($wikiId);

                if ($wiki) {
                    $_SESSION['currentWiki'] = $wikiId;
                    $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(
                        $wikiId,
                        $_SESSION['userdata']['id']
                    );


                    if (is_array($wikiHeadlines) && count($wikiHeadlines) > 0) {
                        $currentArticle = $this->wikiService->getArticle(
                            $wikiHeadlines[0]->id,
                            $_SESSION['currentProject']
                        );

                        $_SESSION['lastArticle'] = $currentArticle->id;
                    } else {
                        $currentArticle = false;
                    }
                }
            } elseif (isset($params['id'])) {
                $currentArticle = $this->wikiService->getArticle($params['id'], $_SESSION['currentProject']);

                if ($currentArticle && $currentArticle->id != null) {
                    $_SESSION['currentWiki'] = $currentArticle->canvasId;
                    $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(
                        $_SESSION['currentWiki'],
                        $_SESSION['userdata']['id']
                    );

                    $_SESSION['lastArticle'] = $currentArticle->id;
                } else {
                    return Frontcontroller::redirect(BASE_URL . "/wiki/show");
                }
            } elseif (isset($_SESSION['lastArticle']) && $_SESSION['lastArticle'] != '') {
                $currentArticle = $this->wikiService->getArticle($_SESSION['lastArticle'], $_SESSION['currentProject']);

                if ($currentArticle) {
                    $_SESSION['currentWiki'] = $currentArticle->canvasId;

                    $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(
                        $_SESSION['currentWiki'],
                        $_SESSION['userdata']['id']
                    );

                    $_SESSION['lastArticle'] = $currentArticle->id;
                    return Frontcontroller::redirect(BASE_URL . "/wiki/show/" . $currentArticle->id);
                }
            } elseif (isset($_SESSION['currentWiki']) && $_SESSION['currentWiki'] > 0) {
                $wikiHeadlines = $this->wikiService->getAllWikiHeadlines($_SESSION['currentWiki'], $_SESSION['userdata']['id']);

                if (is_array($wikiHeadlines) && count($wikiHeadlines) > 0) {
                    $currentArticle = $this->wikiService->getArticle(
                        $wikiHeadlines[0]->id,
                        $_SESSION['currentProject']
                    );

                    $_SESSION['lastArticle'] = $currentArticle->id;
                } else {
                    $currentArticle = false;
                }


                //Last Article is set


                //Nothing is set
            } else {
                if (is_array($wikis) && count($wikis) > 0) {
                    $_SESSION['currentWiki'] = $wikis[0]->id;
                } else {
                    $_SESSION['currentWiki'] = '';
                }

                if ($_SESSION['currentWiki'] != '') {
                    $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(
                        $_SESSION['currentWiki'],
                        $_SESSION['userdata']['id']
                    );
                } else {
                    $wikiHeadlines = array();
                }

                if (is_array($wikiHeadlines) && count($wikiHeadlines) > 0) {
                    $currentArticle = $this->wikiService->getArticle(
                        $wikiHeadlines[0]->id,
                        $_SESSION['currentProject']
                    );

                    $_SESSION['lastArticle'] = $currentArticle->id;
                } else {
                    $currentArticle = false;
                    $_SESSION['lastArticle'] = '';
                }
            }

            if (isset($_SESSION['currentWiki']) && $_SESSION['currentWiki'] != '') {
                $currentWiki = $this->wikiService->getWiki($_SESSION['currentWiki']);
            } else {
                $currentWiki = false;
            }


            //Delete comment
            if (isset($_GET['delComment']) === true) {
                $commentId = (int)($_GET['delComment']);

                $this->commentService->deleteComment($commentId);

                $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success", "wikicomment_deleted");
            }


            if (isset($params['id'])) {
                $comment = $this->commentService->getComments('article', $_GET['id'], 0);
            } elseif (isset($currentArticle->id)) {
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
                $currentArticle = $this->wikiService->getArticle($id, $_SESSION['currentProject']);

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
    }

}
