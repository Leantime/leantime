<?php

namespace leantime\domain\controllers {

    use _PHPStan_ccec86fc8\Nette\Neon\Exception;
    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showAll
    {

        private $projectService;
        private $tpl;
        private $commentService;

        /**
         * @throws Exception
         */
        public function __construct($params)
        {

            $this->tpl = new core\template();
            $this->projectService = new services\projects();
            $this->commentService = new services\comments();


            if(!isset($params['module']) || !isset($params['entitiyId']) || !isset($params['entity'])) {
                throw new Exception("comments module needs to be initialized with module, entity id and entity");
            }

            $this->module = $params['module'];
            $this->id = $params['entitiyId'];
            $this->entity = $params['entity'];




        }

        public function get($params) :void {

            $comments = $this->commentService->getComments($this->module, $this->id, $_SESSION["projectsettings"]['commentOrder']);

            $this->tpl->assign('numComments', count($comments));
            $this->tpl->assign('comments', $comments);

            //Delete comment
            if (isset($params['delComment']) === true) {

                $commentId = (int)($params['delComment']);

                if($this->commentService->deleteComment($commentId)){
                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");
                    $this->tpl->redirect(BASE_URL."/tickets/showTicket/".$this->id);
                }else{
                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted_error"), "error");
                }
            }

            $this->tpl->displayPartial('comments.showAll');

        }

        public function post($params) {

            if (isset($params['comment']) === true) {

                if($this->commentService->addComment($_POST, $this->module, $this->id, $this->entity)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                }else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                }
            }

        }



    }

}
