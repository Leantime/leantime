<?php
/**
 * editCanvasComment class - Generic canvas controller / Edit Comments
 */
namespace leantime\domain\controllers\canvas {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use DateTime;
    use DateInterval;


    class editCanvasComment extends controller
    {

        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';

        private $sprintService;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init()
        {

            $canvasRepoName = "leantime\\domain\\repositories\\".static::CANVAS_NAME.'canvas';
            $this->canvasRepo = new $canvasRepoName();
            $this->sprintService = new services\sprints();
            $this->ticketRepo = new repositories\tickets();
            $this->ticketService = new services\tickets();
            $this->commentsRepo = new repositories\comments();
            $this->projectService = new services\projects();

        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            $canvasTypes = $this->canvasRepo->getCanvasTypes();
            if(isset($params['id'])) {

                // Delete comment
                if(isset($params['delComment']) === true) {
                    $commentId = (int)($params['delComment']);
                    $this->commentsRepo->deleteComment($commentId);
                    $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success');
                }

                $canvasItem = $this->canvasRepo->getSingleCanvasItem($params['id']);

                $comments = $this->commentsRepo->getComments(static::CANVAS_NAME.'canvasitem', $canvasItem['id']);
                $this->tpl->assign('numComments', $this->commentsRepo->countComments(static::CANVAS_NAME.'canvasitem', $canvasItem['id']));

            }else{
                if(isset($params['type'])) {
                    $type = strip_tags($params['type']);
                }else{
                    $type = array_key_first($canvasTypes);
                }

                $canvasItem = array(
                    'id'=>'',
                    'box' => $type,
                    'description' => '',
                    'status' => array_key_first($this->canvasRepo->getStatusList()),
                    'relates' => array_key_first($this->canvasRepo->GetRelatesList()),
                    'assumptions' => '',
                    'data' => '',
                    'conclusion' => '',
                    'milestoneHeadline' => '',
                    'milestoneId' => ''
                );

                $comments = [];

            }

            $this->tpl->assign('comments', $comments);

            $this->tpl->assign('canvasTypes', $canvasTypes);
            $this->tpl->assign('canvasItem', $canvasItem);
            $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.canvasComment');

        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {

            if(isset($params['changeItem'])) {

                if(isset($params['itemId']) && $params['itemId'] != '') {

                    if(isset($params['description']) && !empty($params['description'])) {

                        $currentCanvasId = (int)$_SESSION['current'.strtoupper(static::CANVAS_NAME).'Canvas'];

                        $canvasItem = array(
                            'box' => $params['box'],
                            'author' => $_SESSION['userdata']['id'],
                            'description' => $params['description'],
                            'status' => $params['status'],
                            'relates' => $params['relates'],
                            'assumptions' => $params['assumptions'],
                            'data' => $params['data'],
                            'conclusion' => $params['conclusion'],
                            'itemId' => $params['itemId'],
                            'id' => $params['itemId'],
                            'canvasId' => $currentCanvasId,
                            'milestoneId' => $params['milestoneId'],
                            'dependentMilstone' => ''
                        );

                        $this->canvasRepo->editCanvasComment($canvasItem);

                        $comments = $this->commentsRepo->getComments(static::CANVAS_NAME.'canvasitem', $params['itemId']);
                        $this->tpl->assign('numComments', $this->commentsRepo->countComments(static::CANVAS_NAME.'canvasitem',
                                                                                             $params['itemId']));
                        $this->tpl->assign('comments', $comments);

                        $this->tpl->setNotification($this->language->__('notifications.canvas_item_updates'), 'success');


                        $notification = new models\notifications\notification();
                        $notification->url = array(
                            "url" => BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'/editCanvasComment/'.(int)$params['itemId'],
                            "text" => $this->language->__('email_notifications.canvas_item_update_cta')
                        );
                        $notification->entity = $canvasItem;
                        $notification->module = static::CANVAS_NAME.'canvas';
                        $notification->projectId = $_SESSION['currentProject'];
                        $notification->subject = $this->language->__('email_notifications.canvas_board_edited');
                        $notification->authorId = $_SESSION['userdata']['id'];
                        $notification->message = sprintf($this->language->__('email_notifications.canvas_item_update_message'),
                            $_SESSION['userdata']['name'],  $canvasItem['description']);

                        $this->projectService->notifyProjectUsers($notification);

                        $this->tpl->redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'/editCanvasComment/'.$params['itemId']);

                    }else{
                        $this->tpl->setNotification($this->language->__('notification.please_enter_element_title'), 'error');

                    }

                }else{

                    if(isset($_POST['description']) && !empty($_POST['description'])) {

                        $currentCanvasId = (int)$_SESSION['current'.strtoupper(static::CANVAS_NAME).'Canvas'];

                        $canvasItem = array(
                            'box' => $params['box'],
                            'author' => $_SESSION['userdata']['id'],
                            'description' => $params['description'],
                            'status' => $params['status'],
                            'relates' => $params['relates'],
                            'assumptions' => $params['assumptions'],
                            'data' => $params['data'],
                            'conclusion' => $params['conclusion'],
                            'canvasId' => $currentCanvasId
                        );

                        $id = $this->canvasRepo->addCanvasItem($canvasItem);

                        $canvasItem['id'] = $id;

                        $canvasTypes = $this->canvasRepo->getCanvasTypes();

                        $this->tpl->setNotification($canvasTypes[$params['box']].' successfully created', 'success');


                        $notification = new models\notifications\notification();
                        $notification->url = array(
                            "url" => BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'/editCanvasComment/'.(int)$params['itemId'],
                            "text" => $this->language->__('email_notifications.canvas_item_update_cta')
                        );
                        $notification->entity = $canvasItem;
                        $notification->module = static::CANVAS_NAME.'canvas';
                        $notification->projectId = $_SESSION['currentProject'];
                        $notification->subject = $this->language->__('email_notifications.canvas_board_item_created');
                        $notification->authorId = $_SESSION['userdata']['id'];
                        $notification->message = sprintf($this->language->__('email_notifications.canvas_item_created_message'),
                            $_SESSION['userdata']['name'],  $canvasItem['description']);

                        $this->projectService->notifyProjectUsers($notification);

                        $this->tpl->setNotification($this->language->__('notification.element_created'), 'success');

                        $this->tpl->redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'/editCanvasComment/'.$id);

                    }else{

                        $this->tpl->setNotification($this->language->__('notification.please_enter_element_title'), 'error');

                    }
                }

            }

            if(isset($params['comment']) === true) {

                $values = array(
                    'text' => $params['text'],
                    'date' => date('Y-m-d H:i:s'),
                    'userId' => ($_SESSION['userdata']['id']),
                    'moduleId' => $_GET['id'],
                    'commentParent' => ($params['father'])
                );

                $message = $this->commentsRepo->addComment($values, static::CANVAS_NAME.'canvasitem');
                $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');




                $notification = new models\notifications\notification();
                $notification->url = array(
                    "url" => BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'/editCanvasComment/'.(int)$_GET['id'],
                    "text" => $this->language->__('email_notifications.canvas_item_update_cta')
                );
                $notification->entity = $values;
                $notification->module = static::CANVAS_NAME.'canvas';
                $notification->projectId = $_SESSION['currentProject'];
                $notification->subject = $this->language->__('email_notifications.canvas_board_comment_created');
                $notification->authorId = $_SESSION['userdata']['id'];
                $notification->message = sprintf($this->language->__('email_notifications.canvas_item__comment_created_message'),
                    $_SESSION['userdata']['name']);

                $this->projectService->notifyProjectUsers($notification);


                $this->tpl->redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'/editCanvasComment/'.$_GET['id']);

            }

            $this->tpl->assign('canvasTypes',  $this->canvasRepo->getCanvasTypes());
            $this->tpl->assign('canvasItem',  $this->canvasRepo->getSingleCanvasItem($_GET['id']));
            $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.canvasComment');

        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {

        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {

        }

    }

}
