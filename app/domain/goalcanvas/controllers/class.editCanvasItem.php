<?php

/**
 * Controller / Edit Canvas Item
 */

namespace leantime\domain\controllers {

    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class editCanvasItem extends \leantime\domain\controllers\canvas\editCanvasItem
    {
        protected const CANVAS_NAME = 'goal';
        private repositories\goalcanvas $canvasRepo;
        private services\tickets $ticketService;
        private repositories\comments $commentsRepo;
        private services\projects $projectService;
        private services\goalcanvas $goalService;
        public function init()
        {

            $this->canvasRepo = new repositories\goalcanvas();
            $this->ticketService = new services\tickets();
            $this->commentsRepo = new repositories\comments();
            $this->projectService = new services\projects();
            $this->goalService = new services\goalcanvas();
        }

        public function get($params)
        {
            if (isset($params['id'])) {
                // Delete comment
                if (isset($params['delComment'])) {
                    $commentId = (int)($params['delComment']);
                    $this->commentsRepo->deleteComment($commentId);
                    $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success');
                }

                // Delete milestone relationship
                if (isset($params['removeMilestone'])) {
                    $this->canvasRepo->patchCanvasItem($params['id'], array('milestoneId' => ''));
                    $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), 'success');
                }

                $canvasItem = $this->canvasRepo->getSingleCanvasItem($params['id']);

                if($canvasItem) {
                    $comments = $this->commentsRepo->getComments(
                        'goalcanvasitem',
                        $canvasItem['id']
                    );
                    $this->tpl->assign(
                        'numComments',
                        $this->commentsRepo->countComments(
                            'goalcanvascanvasitem',
                            $canvasItem['id']
                        )
                    );
                }else{
                    $this->tpl->displayPartial('errors.error404');
                    exit();
                }
            } else {


                $canvasItem = array(
                    'id' => '',
                    'box' => "goal",
                    'title' => '',
                    'description' => '',
                    'status' => array_key_first($this->canvasRepo->getStatusLabels()),
                    'relates' => '',
                    'startValue' => '',
                    'currentValue' => '',
                    'canvasId' => (int)$_SESSION['currentGOALCanvas'],
                    'endValue' => '',
                    'kpi' => '',
                    'startDate' => '',
                    'endDate' => '',
                    'setting' => '',
                    'metricType' =>  '',
                    'assignedTo' => '',
                    'parent' => ''
                );

                $comments = [];
            }

            $this->tpl->assign('comments', $comments);

            $this->tpl->assign('availableKPIs', $this->goalService->getParentKPIs($_SESSION['currentProject']));
            $this->tpl->assign('milestones', $this->ticketService->getAllMilestones($_SESSION['currentProject']));

            $this->tpl->assign('canvasItem', $canvasItem);
            $this->tpl->assign('canvasIcon', $this->canvasRepo->getIcon());
            $this->tpl->assign('canvasTypes', $this->canvasRepo->getCanvasTypes());
            $this->tpl->assign('statusLabels', $this->canvasRepo->getStatusLabels());
            $this->tpl->assign('dataLabels', $this->canvasRepo->getDataLabels());
            $this->tpl->displayPartial('goalcanvas.canvasDialog');
        }

        public function post($params)
        {
            if (isset($params['changeItem'])) {
                $currentCanvasId = (int)$_SESSION['current' . strtoupper(static::CANVAS_NAME) . 'Canvas'];

                if (isset($params['itemId']) && !empty($params['itemId'])) {
                    if (isset($params['title']) && !empty($params['title'])) {
                        $canvasItem = array(
                            'box' => $params['box'],
                            'author' => $_SESSION['userdata']['id'],
                            'title' => $params['title'],
                            'description' => $params['description'] ?? '',
                            'status' => $params['status'],
                            'relates' => '',
                            'startValue' => $params['startValue'],
                            'currentValue' => $params['currentValue'],
                            'endValue' => $params['endValue'],
                            'itemId' => $params['itemId'],
                            'canvasId' => $currentCanvasId,
                            'parent' => $params['parent'] ?? null,
                            "id" => $params['itemId'],
                            'kpi' => $params['kpi'] ?? '',
                            'startDate' => $this->language->getISODateString($params['startDate']),
                            'endDate' => $this->language->getISODateString($params['endDate']),
                            'setting' => $params['setting'] ?? '',
                            'metricType' =>  $params['metricType'],
                            'assignedTo' => $params['assignedTo'] ?? ''
                        );

                        if (isset($params['newMilestone']) && $params['newMilestone'] != '') {
                            $params['headline'] = $params['newMilestone'];
                            $params['tags'] = '#ccc';
                            $params['editFrom'] = date('Y-m-d');
                            $params['editTo'] = date('Y-m-d', strtotime('+1 week'));
                            $params['dependentMilestone'] = '';
                            $id = $this->ticketService->quickAddMilestone($params);

                            if ($id !== false) {
                                $canvasItem['milestoneId'] = $id;
                            }
                        }
                        if (isset($params['existingMilestone']) && $params['existingMilestone'] != '') {
                            $canvasItem['milestoneId'] = $params['existingMilestone'];
                        }

                        $this->canvasRepo->editCanvasItem($canvasItem);

                        $comments = $this->commentsRepo->getComments('goalcanvasitem', $params['itemId']);
                        $this->tpl->assign('numComments', $this->commentsRepo->countComments(
                            'goalcanvasitem',
                            $params['itemId']
                        ));
                        $this->tpl->assign('comments', $comments);

                        $this->tpl->setNotification($this->language->__('notifications.canvas_item_updates'), 'success');

                        $subject = $this->language->__('email_notifications.canvas_board_edited');
                        $actual_link = BASE_URL . '/goalcanvas/editCanvasItem/' . (int)$params['itemId'];
                        $message = sprintf(
                            $this->language->__('email_notifications.canvas_item_update_message'),
                            $_SESSION['userdata']['name'],
                            $canvasItem['description']
                        );

                        $notification = new models\notifications\notification();
                        $notification->url = array(
                            "url" => $actual_link,
                            "text" => $this->language->__('email_notifications.canvas_item_update_cta')
                        );
                        $notification->entity = $canvasItem;
                        $notification->module = 'goalcanvas';
                        $notification->projectId = $_SESSION['currentProject'];
                        $notification->subject = $subject;
                        $notification->authorId = $_SESSION['userdata']['id'];
                        $notification->message = $message;

                        $this->projectService->notifyProjectUsers($notification);
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                    }

                    $this->tpl->redirect(BASE_URL . '/goalcanvas/editCanvasItem/' . $params['itemId']);
                } else {
                    if (isset($_POST['title']) && !empty($_POST['title'])) {

                        $canvasItem = array(
                            'box' => $params['box'],
                            'author' => $_SESSION['userdata']['id'],
                            'title' => $params['title'],
                            'description' => $params['description'] ?? '',
                            'status' => $params['status'],
                            'relates' => '',
                            'startValue' => $params['startValue'],
                            'currentValue' => $params['currentValue'],
                            'endValue' => $params['endValue'],
                            'canvasId' => $currentCanvasId,
                            'parent' => $params['parent'] ?? null,
                            'kpi' => $params['kpi'] ?? '',
                            'startDate' => $this->language->getISODateString($params['startDate']),
                            'endDate' => $this->language->getISODateString($params['endDate']),
                            'setting' => $params['setting'] ?? '',
                            'metricType' =>  $params['metricType'],
                            'assignedTo' => $params['assignedTo'] ?? ''
                        );

                        $id = $this->canvasRepo->addCanvasItem($canvasItem);
                        $canvasTypes = $this->canvasRepo->getCanvasTypes();

                        $this->tpl->setNotification($canvasTypes[$params['box']]['title'] . ' successfully created', 'success');

                        $subject = $this->language->__('email_notifications.canvas_board_item_created');
                        $actual_link = BASE_URL . '/goalcanvas/editCanvasItem/' . (int)$params['itemId'];
                        $message = sprintf(
                            $this->language->__('email_notifications.canvas_item_created_message'),
                            $_SESSION['userdata']['name'],
                            $canvasItem['description']
                        );

                        $notification = new models\notifications\notification();
                        $notification->url = array(
                            "url" => $actual_link,
                            "text" => $this->language->__('email_notifications.canvas_item_update_cta')
                        );

                        $notification->entity = $canvasItem;
                        $notification->module = 'goalcanvas';
                        $notification->projectId = $_SESSION['currentProject'];
                        $notification->subject = $subject;
                        $notification->authorId = $_SESSION['userdata']['id'];
                        $notification->message = $message;

                        $this->projectService->notifyProjectUsers($notification);

                        $this->tpl->setNotification($this->language->__('notification.element_created'), 'success');
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                    }

                    $this->tpl->redirect(BASE_URL . '/goalcanvas/editCanvasItem/' . $id);
                }
            }

            if (isset($params['comment'])) {
                $values = array(
                    'text' => $params['text'],
                    'date' => date('Y-m-d H:i:s'),
                    'userId' => ($_SESSION['userdata']['id']),
                    'moduleId' => $_GET['id'],
                    'commentParent' => ($params['father'])
                );

                if($params['text'] != '') {
                    $commentId = $this->commentsRepo->addComment($values, 'goalcanvasitem');
                    $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');
                    $values['id'] = $commentId;

                    $subject = $this->language->__('email_notifications.canvas_board_comment_created');
                    $actual_link = BASE_URL . '/goalcanvas/editCanvasItem/' . (int)$_GET['id'];
                    $message = sprintf(
                        $this->language->__('email_notifications.canvas_item__comment_created_message'),
                        $_SESSION['userdata']['name']
                    );

                    $notification = new models\notifications\notification();
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__('email_notifications.canvas_item_update_cta')
                    );
                    $notification->entity = $values;
                    $notification->module = 'goalcanvas';
                    $notification->projectId = $_SESSION['currentProject'];
                    $notification->subject = $subject;
                    $notification->authorId = $_SESSION['userdata']['id'];
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    $this->tpl->redirect(BASE_URL . '/goalcanvas/editCanvasItem/' . $_GET['id']);
                }
            }


            $this->tpl->assign('canvasTypes', $this->canvasRepo->getCanvasTypes());
            $this->tpl->assign('statusLabels', $this->canvasRepo->getStatusLabels());

            $this->tpl->assign('dataLabels', $this->canvasRepo->getDataLabels());

            if (isset($_GET['id'])) {
                $comments = $this->commentsRepo->getComments('goalcanvasitem', $_GET['id']);
                $this->tpl->assign('canvasItem', $this->canvasRepo->getSingleCanvasItem($_GET['id']));
            } else {
                $value = array(
                    'id' => '',
                    'box' => $params['box'],
                    'author' => $_SESSION['userdata']['id'],
                    'title' => '',
                    'description' => '',
                    'status' => array_key_first($this->canvasRepo->getStatusLabels()),
                    'relates' => array_key_first($this->canvasRepo->getRelatesLabels()),
                    'startValue' => '',
                    'currentValue' => '',
                    'endValue' => '',
                    'kpi' => '',
                    'startDate' => '',
                    'endDate' => '',
                    'setting ' => '',
                    'metricType' =>  '',
                    'assignedTo' => $_SESSION['userdata']['id'],
                );
                $comments = array();
                $this->tpl->assign('canvasItem', $value);
            }
            $this->tpl->assign('comments', $comments);
            $this->tpl->displayPartial('goalcanvas.editCanvasItem');
        }

    }

}
