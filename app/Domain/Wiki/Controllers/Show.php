<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Wiki\Models\Wiki;
    use Leantime\Domain\Wiki\Services\Wiki as WikiService;
    use Symfony\Component\HttpFoundation\Response;

    class Show extends Controller
    {
        private WikiService $wikiService;

        private CommentService $commentService;

        public function init(WikiService $wikiService, CommentService $commentService): void
        {
            $this->wikiService = $wikiService;
            $this->commentService = $commentService;
        }

        /**
         * @throws BindingResolutionException
         */
        public function get($params): Response
        {

            $currentArticle = '';
            $wikiHeadlines = [];

            $wikis = $this->wikiService->getAllProjectWikis(session('currentProject'));
            if (! $wikis || count($wikis) == 0) {
                $wiki = app()->make(Wiki::class);
                $wiki->title = $this->language->__('label.default');
                $wiki->projectId = session('currentProject');
                $wiki->author = session('userdata.id');

                $id = $this->wikiService->createWiki($wiki);
                $wikis = $this->wikiService->getAllProjectWikis(session('currentProject'));
            }

            //Option 1: Setting wiki (active action), set wiki, headlines and current Article
            if (isset($_GET['setWiki'])) {
                session()->forget('lastArticle');
                $wikiId = (int) $_GET['setWiki'];

                $wiki = $this->wikiService->getWiki($wikiId);

                if ($wiki) {
                    session(['currentWiki' => $wikiId]);
                    $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(
                        $wikiId,
                        session('userdata.id')
                    );

                    if (is_array($wikiHeadlines) && count($wikiHeadlines) > 0) {
                        $currentArticle = $this->wikiService->getArticle(
                            $wikiHeadlines[0]->id,
                            session('currentProject')
                        );

                        session(['lastArticle' => $currentArticle->id]);
                    } else {
                        $currentArticle = false;
                    }
                }
            } elseif (isset($params['id'])) {
                $currentArticle = $this->wikiService->getArticle($params['id'], session('currentProject'));

                if ($currentArticle && $currentArticle->id != null) {
                    session(['currentWiki' => $currentArticle->canvasId]);
                    $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(
                        session('currentWiki'),
                        session('userdata.id')
                    );

                    session(['lastArticle' => $currentArticle->id]);
                } else {
                    return Frontcontroller::redirect(BASE_URL.'/wiki/show');
                }
            } elseif (session()->exists('lastArticle') && session('lastArticle') != '') {
                $currentArticle = $this->wikiService->getArticle(session('lastArticle'), session('currentProject'));

                if ($currentArticle) {
                    session(['currentWiki' => $currentArticle->canvasId]);

                    $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(
                        session('currentWiki'),
                        session('userdata.id')
                    );

                    session(['lastArticle' => $currentArticle->id]);

                    return Frontcontroller::redirect(BASE_URL.'/wiki/show/'.$currentArticle->id);
                }
            } elseif (session()->exists('currentWiki') && session('currentWiki') > 0) {
                $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(session('currentWiki'), session('userdata.id'));

                if (is_array($wikiHeadlines) && count($wikiHeadlines) > 0) {
                    $currentArticle = $this->wikiService->getArticle(
                        $wikiHeadlines[0]->id,
                        session('currentProject')
                    );

                    session(['lastArticle' => $currentArticle->id]);
                } else {
                    $currentArticle = false;
                }

                //Last Article is set

                //Nothing is set
            } else {
                if (is_array($wikis) && count($wikis) > 0) {
                    session(['currentWiki' => $wikis[0]->id]);
                } else {
                    session(['currentWiki' => '']);
                }

                if (session('currentWiki') != '') {
                    $wikiHeadlines = $this->wikiService->getAllWikiHeadlines(
                        session('currentWiki'),
                        session('userdata.id')
                    );
                } else {
                    $wikiHeadlines = [];
                }

                if (is_array($wikiHeadlines) && count($wikiHeadlines) > 0) {
                    $currentArticle = $this->wikiService->getArticle(
                        $wikiHeadlines[0]->id,
                        session('currentProject')
                    );

                    session(['lastArticle' => $currentArticle->id]);
                } else {
                    $currentArticle = false;
                    session(['lastArticle' => '']);
                }
            }

            if (session()->exists('currentWiki') && session('currentWiki') != '') {
                $currentWiki = $this->wikiService->getWiki(session('currentWiki'));
            } else {
                $currentWiki = false;
            }

            //Delete comment
            if (isset($_GET['delComment']) === true) {
                $commentId = (int) ($_GET['delComment']);

                $this->commentService->deleteComment($commentId);

                $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success', 'wikicomment_deleted');
            }

            if (isset($params['id'])) {
                $comment = $this->commentService->getComments('article', $params['id'], 0);
            } elseif (isset($currentArticle->id)) {
                $comment = $this->commentService->getComments('article', $currentArticle->id, 0);
            } else {
                $comment = [];
            }

            $this->tpl->assign('comments', $comment);
            $this->tpl->assign('numComments', count($comment));

            $this->tpl->assign('currentArticle', $currentArticle);
            $this->tpl->assign('currentWiki', $currentWiki);
            $this->tpl->assign('wikis', $wikis);
            $this->tpl->assign('wikiHeadlines', $wikiHeadlines);

            return $this->tpl->display('wiki.show');
        }

        /**
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {

            if (isset($_GET['id']) === true) {
                $id = (int) ($_GET['id']);
                $currentArticle = $this->wikiService->getArticle($id, session('currentProject'));

                if (isset($_POST['comment']) === true) {
                    if ($this->commentService->addComment($_POST, 'article', $id, $currentArticle)) {
                        $this->tpl->setNotification(
                            $this->language->__('notifications.comment_create_success'),
                            'success',
                            'wikicomment_created'
                        );
                    } else {
                        $this->tpl->setNotification($this->language->__('notifications.comment_create_error'), 'error');
                    }
                }

                return Frontcontroller::redirect(BASE_URL.'/wiki/show/'.$id);
            }

            return Frontcontroller::redirect(BASE_URL.'/wiki/show/');
        }
    }

}
