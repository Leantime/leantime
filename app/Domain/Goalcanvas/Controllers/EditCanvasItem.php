<?php

/**
 * Controller / Edit Canvas Item
 */

namespace Leantime\Domain\Goalcanvas\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvaService;
    use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Symfony\Component\HttpFoundation\Response;

    class EditCanvasItem extends Controller
    {
        private TicketService $ticketService;

        private ProjectService $projectService;

        private GoalcanvaService $goalService;

        /**
         * @param  GoalcanvaRepository  $canvasRepo
         * @param  CommentRepository  $commentsRepo
         */
        public function init(

            TicketService $ticketService,
            ProjectService $projectService,
            GoalcanvaService $goalService
        ): void {

            $this->ticketService = $ticketService;
            $this->projectService = $projectService;
            $this->goalService = $goalService;
        }

        /**
         * @throws \Exception
         */
        public function get($params): Response
        {
            $result = $this->goalService->getCanvasData($params);

            if ($result === false) {
                return $this->tpl->displayPartial('errors.error404');
            }

            $this->tpl->assign('id', $result['canvasItem']['id'] ?? '');
            $this->tpl->assign('canvasId', $result['canvasItem']['canvasId']);
            $this->tpl->assign('comments', $result['comments']);
            $this->tpl->assign('numComments', $result['numComments']);
            $this->tpl->assign('currentCanvas', $result['canvasItem']['canvasId']);
            $this->tpl->assign('canvasItem', $result['canvasItem']);
            $this->tpl->assign('canvasIcon', $result['canvasIcon']);
            $this->tpl->assign('canvasTypes', $result['canvasTypes']);
            $this->tpl->assign('statusLabels', $result['statusLabels']);
            $this->tpl->assign('dataLabels', $result['dataLabels']);

            $allProjectMilestones = $this->ticketService->getAllMilestones([
                'sprint' => '',
                'type' => 'milestone',
                'currentProject' => session('currentProject'),
            ]);
            $this->tpl->assign('milestones', $allProjectMilestones);

            return $this->tpl->displayPartial('goalcanvas::partials.canvasDialog');
        }

        /**
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {
            if (isset($params['comment'])) {
                $commentId = $this->goalService->addComment($params);
                if ($commentId) {
                    $this->notifyOnCommentCreation($params, $commentId);
                    $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');

                    return Frontcontroller::redirect(BASE_URL.'/goalcanvas/editCanvasItem/'.$_GET['id']);
                }
            }

            if (isset($params['changeItem'])) {

                $result = $this->goalService->updateCanvasItem($params);
                if ($result) {
                    $this->notifyOnItemUpdate($result['canvasItem']);
                    $canvasTypes = $this->goalService->getCanvasTypes();
                    $this->tpl->setNotification($canvasTypes[$params['box']]['title'].' successfully '.($result['isNew'] ? 'created' : 'updated'), 'success');

                    $this->tpl->closeModal();
                    $this->tpl->htmxRefresh();

                    return $this->tpl->emptyResponse();

                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            $this->prepareView($params);

            return $this->tpl->displayPartial('goalcanvas::partials.editCanvasItem');
        }

        private function notifyOnCommentCreation($params, $commentId): void
        {
            $notification = app()->make(NotificationModel::class);
            $notification->url = [
                'url' => BASE_URL.'/goalcanvas/editCanvasItem/'.(int) $_GET['id'],
                'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
            ];
            $notification->entity = array_merge($params, ['id' => $commentId]);
            $notification->module = 'goalcanvas';
            $notification->projectId = session('currentProject');
            $notification->subject = $this->language->__('email_notifications.canvas_board_comment_created');
            $notification->authorId = session('userdata.id');
            $notification->message = sprintf(
                $this->language->__('email_notifications.canvas_item__comment_created_message'),
                session('userdata.name')
            );

            $this->projectService->notifyProjectUsers($notification);
        }

        private function notifyOnItemUpdate($canvasItem): void
        {
            $notification = app()->make(NotificationModel::class);
            $notification->url = [
                'url' => BASE_URL.'/goalcanvas/editCanvasItem/'.(int) $canvasItem['id'],
                'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
            ];
            $notification->entity = $canvasItem;
            $notification->module = 'goalcanvas';
            $notification->projectId = session('currentProject');
            $notification->subject = $this->language->__('email_notifications.canvas_board_edited');
            $notification->authorId = session('userdata.id');
            $notification->message = sprintf(
                $this->language->__('email_notifications.canvas_item_update_message'),
                session('userdata.name'),
                $canvasItem['description']
            );

            $this->projectService->notifyProjectUsers($notification);
        }

        private function prepareView($params): void
        {
            $this->tpl->assign('canvasTypes', $this->goalService->getCanvasTypes());
            $this->tpl->assign('statusLabels', $this->goalService->getStatusLabels());
            $this->tpl->assign('dataLabels', $this->goalService->getDataLabels());

            if (isset($_GET['id'])) {
                $canvasItemData = $this->goalService->getCanvasItemData($_GET['id']);
                $this->tpl->assign('canvasItem', $canvasItemData['canvasItem']);
                $this->tpl->assign('comments', $canvasItemData['comments']);
            } else {
                $this->tpl->assign('canvasItem', $this->goalService->getNewCanvasItemTemplate($params));
                $this->tpl->assign('comments', []);
            }
        }
    }
}
