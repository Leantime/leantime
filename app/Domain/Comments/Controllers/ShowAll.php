<?php

namespace Leantime\Domain\Comments\Controllers {

    use Exception;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Auth\Services\Auth;

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
         * @return void
         * @throws Exception
         */
        /**
         * @param $params
         * @return void
         * @throws Exception
         */
        public function get($params): void
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
                    $this->tpl->redirect(BASE_URL . "/tickets/showTicket/" . $this->id);
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted_error"), "error");
                }
            }

            $this->tpl->displayPartial('comments.showAll');
        }

        /**
         * @param $params
         * @return void
         * @throws BindingResolutionException
         */
        /**
         * @param $params
         * @return void
         * @throws BindingResolutionException
         */
        public function post($params): void
        {

            if (isset($params['comment']) === true) {
                if ($this->commentService->addComment($_POST, $this->module, $this->id, $this->entity)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                }
            }
        }
    }

}
