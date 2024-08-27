<?php

/**
 * editCanvasComment class - Generic canvas controller / Edit Comments
 */

namespace Leantime\Domain\Canvas\Controllers {

    use Illuminate\Support\Str;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;

    /**
     *
     */
    class EditCanvasComment extends Controller
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';

        private TicketRepository $ticketRepo;
        private CommentRepository $commentsRepo;
        private SprintService $sprintService;
        private TicketService $ticketService;
        private ProjectService $projectService;
        private object $canvasRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            TicketRepository $ticketRepo,
            CommentRepository $commentsRepo,
            SprintService $sprintService,
            TicketService $ticketService,
            ProjectService $projectService
        ) {
            $this->ticketRepo = $ticketRepo;
            $this->commentsRepo = $commentsRepo;
            $this->sprintService = $sprintService;
            $this->ticketService = $ticketService;
            $this->projectService = $projectService;

            $canvasName = Str::studly(static::CANVAS_NAME) . 'canvas';
            $repoName = app()->getNamespace() . "Domain\\$canvasName\\Repositories\\$canvasName";
            $this->canvasRepo = app()->make($repoName);
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
            if (isset($params['id'])) {
                // Delete comment
                if (isset($params['delComment']) === true) {
                    $commentId = (int)($params['delComment']);
                    $this->commentsRepo->deleteComment($commentId);
                    $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success', strtoupper(static::CANVAS_NAME) . 'canvascomment_deleted');
                }

                $canvasItem = $this->canvasRepo->getSingleCanvasItem($params['id']);

                $comments = $this->commentsRepo->getComments(static::CANVAS_NAME . 'canvasitem', $canvasItem['id']);
                $this->tpl->assign('numComments', $this->commentsRepo->countComments(static::CANVAS_NAME . 'canvasitem', $canvasItem['id']));
            } else {
                if (isset($params['type'])) {
                    $type = strip_tags($params['type']);
                } else {
                    $type = array_key_first($canvasTypes);
                }

                $canvasItem = array(
                    'id' => '',
                    'box' => $type,
                    'description' => '',
                    'status' => array_key_first($this->canvasRepo->getStatusList()),
                    'relates' => array_key_first($this->canvasRepo->GetRelatesList()),
                    'assumptions' => '',
                    'data' => '',
                    'conclusion' => '',
                    'milestoneHeadline' => '',
                    'milestoneId' => '',
                );

                $comments = [];
            }

            $this->tpl->assign('comments', $comments);

            $this->tpl->assign('canvasTypes', $canvasTypes);
            $this->tpl->assign('canvasItem', $canvasItem);
            return $this->tpl->displayPartial(static::CANVAS_NAME . 'canvas.canvasComment');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {

            if (isset($params['changeItem'])) {
                if (isset($params['itemId']) && $params['itemId'] != '') {
                    if (isset($params['description']) && !empty($params['description'])) {
                        $currentCanvasId = (int)session("current" . strtoupper(static::CANVAS_NAME) . "Canvas");

                        $canvasItem = array(
                            'box' => $params['box'],
                            'author' => session("userdata.id"),
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
                            'dependentMilstone' => '',
                        );

                        $this->canvasRepo->editCanvasComment($canvasItem);

                        $comments = $this->commentsRepo->getComments(static::CANVAS_NAME . 'canvasitem', $params['itemId']);
                        $this->tpl->assign('numComments', $this->commentsRepo->countComments(
                            static::CANVAS_NAME . 'canvasitem',
                            $params['itemId']
                        ));
                        $this->tpl->assign('comments', $comments);

                        $this->tpl->setNotification($this->language->__('notifications.canvas_item_updates'), 'success', strtoupper(static::CANVAS_NAME) . 'canvasitem_updated');


                        $notification = app()->make(NotificationModel::class);
                        $notification->url = array(
                            "url" => BASE_URL . '/' . static::CANVAS_NAME . 'canvas' . '/editCanvasComment/' . (int)$params['itemId'],
                            "text" => $this->language->__('email_notifications.canvas_item_update_cta'),
                        );
                        $notification->entity = $canvasItem;
                        $notification->module = static::CANVAS_NAME . 'canvas';
                        $notification->projectId = session("currentProject");
                        $notification->subject = $this->language->__('email_notifications.canvas_board_edited');
                        $notification->authorId = session("userdata.id");
                        $notification->message = sprintf(
                            $this->language->__('email_notifications.canvas_item_update_message'),
                            session("userdata.name"),
                            $canvasItem['description']
                        );

                        $this->projectService->notifyProjectUsers($notification);

                        return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas' . '/editCanvasComment/' . $params['itemId']);
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.please_enter_element_title'), 'error');
                    }
                } else {
                    if (isset($_POST['description']) && !empty($_POST['description'])) {
                        $currentCanvasId = (int)session("current" . strtoupper(static::CANVAS_NAME) . "Canvas");

                        $canvasItem = array(
                            'box' => $params['box'],
                            'author' => session("userdata.id"),
                            'description' => $params['description'],
                            'status' => $params['status'],
                            'relates' => $params['relates'],
                            'assumptions' => $params['assumptions'],
                            'data' => $params['data'],
                            'conclusion' => $params['conclusion'],
                            'canvasId' => $currentCanvasId,
                        );

                        $id = $this->canvasRepo->addCanvasItem($canvasItem);

                        $canvasItem['id'] = $id;

                        $canvasTypes = $this->canvasRepo->getCanvasTypes();

                        $this->tpl->setNotification($canvasTypes[$params['box']] . ' successfully created', 'success', strtoupper(static::CANVAS_NAME) . 'canvasitem_created');


                        $notification = app()->make(NotificationModel::class);
                        $notification->url = array(
                            "url" => BASE_URL . '/' . static::CANVAS_NAME . 'canvas' . '/editCanvasComment/' . (int)$params['itemId'],
                            "text" => $this->language->__('email_notifications.canvas_item_update_cta'),
                        );
                        $notification->entity = $canvasItem;
                        $notification->module = static::CANVAS_NAME . 'canvas';
                        $notification->projectId = session("currentProject");
                        $notification->subject = $this->language->__('email_notifications.canvas_board_item_created');
                        $notification->authorId = session("userdata.id");
                        $notification->message = sprintf(
                            $this->language->__('email_notifications.canvas_item_created_message'),
                            session("userdata.name"),
                            $canvasItem['description']
                        );

                        $this->projectService->notifyProjectUsers($notification);

                        $this->tpl->setNotification($this->language->__('notification.element_created'), 'success', strtoupper(static::CANVAS_NAME) . 'canvasitem_created');

                        return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas' . '/editCanvasComment/' . $id);
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.please_enter_element_title'), 'error');
                    }
                }
            }

            if (isset($params['comment']) === true) {
                $values = array(
                    'text' => $params['text'],
                    'date' => date('Y-m-d H:i:s'),
                    'userId' => (session("userdata.id")),
                    'moduleId' => $_GET['id'],
                    'commentParent' => ($params['father']),
                );

                $message = $this->commentsRepo->addComment($values, static::CANVAS_NAME . 'canvasitem');
                $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success', strtoupper(static::CANVAS_NAME) . 'canvasitemcomment_created');

                $notification = app()->make(NotificationModel::class);
                $notification->url = array(
                    "url" => BASE_URL . '/' . static::CANVAS_NAME . 'canvas' . '/editCanvasComment/' . (int)$_GET['id'],
                    "text" => $this->language->__('email_notifications.canvas_item_update_cta'),
                );
                $notification->entity = $values;
                $notification->module = static::CANVAS_NAME . 'canvas';
                $notification->projectId = session("currentProject");
                $notification->subject = $this->language->__('email_notifications.canvas_board_comment_created');
                $notification->authorId = session("userdata.id");
                $notification->message = sprintf(
                    $this->language->__('email_notifications.canvas_item__comment_created_message'),
                    session("userdata.name")
                );

                $this->projectService->notifyProjectUsers($notification);


                return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas' . '/editCanvasComment/' . $_GET['id']);
            }

            $this->tpl->assign('id', $_GET['id']);
            $this->tpl->assign('canvasTypes', $this->canvasRepo->getCanvasTypes());
            $this->tpl->assign('canvasItem', $this->canvasRepo->getSingleCanvasItem($_GET['id']));
            return $this->tpl->displayPartial(static::CANVAS_NAME . 'canvas.canvasComment');
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
