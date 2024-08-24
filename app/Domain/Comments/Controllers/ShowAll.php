<?php

namespace Leantime\Domain\Comments\Controllers {

    use Exception;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class ShowAll extends Controller
    {
        private ProjectService $projectService;
        private CommentService $commentService;
        private $module;
        private $id;
        private $entity;

        /**
         * init - initialize private variables
         *
         * @access public
         * @throws Exception
         */
        public function init(
            ProjectService $projectService,
            CommentService $commentService
        ): void {
            $this->projectService = $projectService;
            $this->commentService = $commentService;
        }

        /**
         * @param $params
         * @return Response
         * @throws Exception
         */
        public function get($params): Response
        {
            if (!isset($params['module'], $params['entitiyId'], $params['entity'])) {
                throw new Exception("comments module needs to be initialized with module, entity id and entity");
            }

            $this->module = $params['module'];
            $this->id = $params['entitiyId'];
            $this->entity = $params['entity'];

            $comments = $this->commentService->getComments($this->module, $this->id);

            $this->tpl->assign('numComments', count($comments));
            $this->tpl->assign('comments', $comments);

            //Delete comment
            if (isset($params['delComment']) === true) {
                $commentId = (int)($params['delComment']);

                if ($this->commentService->deleteComment($commentId)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");
                    return Frontcontroller::redirect(BASE_URL . "/tickets/showTicket/" . $this->id);
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted_error"), "error");
                }
            }

            return $this->tpl->displayPartial('comments.showAll');
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {
            if (isset($params['comment']) === true) {
                if ($this->commentService->addComment($_POST, $this->module, $this->id, $this->entity)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                }
            }

            return new Response();
        }
    }

}
