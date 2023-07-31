<?php

namespace leantime\domain\controllers {

    use Exception;
    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showAll extends controller
    {
        private services\projects $projectService;
        private services\comments $commentService;
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
            services\projects $projectService,
            services\comments $commentService
        ): void {
            $this->projectService = $projectService;
            $this->commentService = $commentService;
        }

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
