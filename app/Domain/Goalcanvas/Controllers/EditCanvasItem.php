<?php

/**
 * Controller / Edit Canvas Item
 */

namespace Leantime\Domain\Goalcanvas\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Goalcanvas\Permissions\GoalcanvasPermissions;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvaService;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Goal canvas item editor. Standalone (own get()/post()), independent of the canvas domain.
 */
class EditCanvasItem extends Controller
{
    protected const CANVAS_NAME = 'goal';

    private GoalcanvaRepository $canvasRepo;

    private CommentRepository $commentsRepo;

    private TicketService $ticketService;

    private ProjectService $projectService;

    private GoalcanvaService $goalService;

    public function init(
        GoalcanvaRepository $canvasRepo,
        CommentRepository $commentsRepo,
        TicketService $ticketService,
        ProjectService $projectService,
        GoalcanvaService $goalService
    ): void {
        $this->canvasRepo = $canvasRepo;
        $this->commentsRepo = $commentsRepo;
        $this->ticketService = $ticketService;
        $this->projectService = $projectService;
        $this->goalService = $goalService;
    }

    /**
     * @throws \Exception
     */
    #[RequiresPermission(GoalcanvasPermissions::VIEW, entityScoped: true)]
    public function get($params): Response
    {
        if (isset($params['id'])) {
            // Resolve + VIEW-authorize the item against its real project BEFORE any mutation.
            // false = missing / foreign project / unauthorized (indistinguishable -> no oracle).
            $canvasItem = $this->goalService->getGoalItem((int) $params['id']);
            if (! $canvasItem) {
                return $this->tpl->displayPartial('errors.error404');
            }

            // Delete comment — only when it belongs to THIS gated item (module + moduleId);
            // deleteComment() filters on the comment id alone, so the bind prevents deleting a
            // foreign item's / project's comment.
            if (isset($params['delComment'])) {
                $commentId = (int) ($params['delComment']);
                $comment = $this->commentsRepo->getComment($commentId);
                if ($comment !== false
                    && (string) $comment['module'] === 'goalcanvasitem'
                    && (int) $comment['moduleId'] === (int) $canvasItem['id']) {
                    $this->commentsRepo->deleteComment($commentId);
                    $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success');
                }
            }

            // Delete milestone relationship — an EDIT, authorized by the service against the
            // item's project (a view-only user is denied here).
            if (isset($params['removeMilestone'])) {
                $this->goalService->patchGoalItem((int) $params['id'], ['milestoneId' => '']);
                $canvasItem = $this->goalService->getGoalItem((int) $params['id']);
                $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), 'success');
            }

            $comments = $this->commentsRepo->getComments('goalcanvasitem', $canvasItem['id']);
            $this->tpl->assign(
                'numComments',
                $this->commentsRepo->countComments('goalcanvasitem', $canvasItem['id'])
            );
        } else {
            $canvasItem = [
                'id' => '',
                'box' => 'goal',
                'title' => '',
                'description' => '',
                'status' => array_key_first($this->canvasRepo->getStatusLabels()),
                'relates' => '',
                'startValue' => '',
                'currentValue' => '',
                'canvasId' => $_GET['canvasId'] ?? (int) session('currentGOALCanvas'),
                'endValue' => '',
                'kpi' => '',
                'startDate' => '',
                'endDate' => '',
                'setting' => '',
                'metricType' => '',
                'assignedTo' => '',
                'parent' => '',
            ];

            $comments = [];
        }

        $this->tpl->assign('id', $canvasItem['id'] ?? '');
        $this->tpl->assign('canvasId', $canvasItem['canvasId']);
        $this->tpl->assign('comments', $comments);

        $allProjectMilestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
        $this->tpl->assign('milestones', $allProjectMilestones);

        $this->tpl->assign('currentCanvas', $canvasItem['canvasId']);
        $this->tpl->assign('canvasItem', $canvasItem);
        $this->tpl->assign('canvasIcon', $this->canvasRepo->getIcon());
        $this->tpl->assign('canvasTypes', $this->canvasRepo->getCanvasTypes());
        $this->tpl->assign('statusLabels', $this->canvasRepo->getStatusLabels());
        $this->tpl->assign('dataLabels', $this->canvasRepo->getDataLabels());

        return $this->tpl->displayPartial('goalcanvas.canvasDialog');
    }

    /**
     * @throws BindingResolutionException
     */
    #[RequiresPermission(GoalcanvasPermissions::EDIT, entityScoped: true)]
    public function post($params): Response
    {

        if (isset($params['comment']) && isset($params['id'])) {
            $itemId = (int) $params['id'];

            // Only allow commenting on a goal item the user can view in their project.
            if (! $this->goalService->getGoalItem($itemId)) {
                return $this->tpl->displayPartial('errors.error404');
            }

            $values = [
                'text' => $params['text'],
                'date' => date('Y-m-d H:i:s'),
                'userId' => (session('userdata.id')),
                'moduleId' => $itemId,
                'commentParent' => ($params['father']),
            ];

            if ($params['text'] != '') {
                $commentId = $this->commentsRepo->addComment($values, 'goalcanvasitem');
                $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');
                $values['id'] = $commentId;

                $subject = $this->language->__('email_notifications.canvas_board_comment_created');
                $actual_link = BASE_URL.'#/goalcanvas/editCanvasItem/'.$itemId;
                $message = sprintf(
                    $this->language->__('email_notifications.canvas_item__comment_created_message'),
                    session('userdata.name')
                );

                $notification = app()->make(NotificationModel::class);
                $notification->url = [
                    'url' => $actual_link,
                    'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
                ];
                $notification->entity = $values;
                $notification->module = 'goalcanvas';
                $notification->action = 'commented';
                $notification->projectId = session('currentProject');
                $notification->subject = $subject;
                $notification->authorId = session('userdata.id');
                $notification->message = $message;

                $this->projectService->notifyProjectUsers($notification);

                return Frontcontroller::redirect(BASE_URL.'/goalcanvas/editCanvasItem/'.$itemId);
            }
        }

        if (isset($params['changeItem'])) {
            $currentCanvasId = $params['canvasId'] ?? (int) session('current'.strtoupper(static::CANVAS_NAME).'Canvas');

            if (isset($params['itemId']) && ! empty($params['itemId'])) {
                if (isset($params['title']) && ! empty($params['title'])) {
                    $canvasItem = [
                        'box' => $params['box'],
                        'author' => session('userdata.id'),
                        'title' => $params['title'],
                        'description' => $params['description'] ?? '',
                        'status' => $params['status'] ?? '',
                        'relates' => '',
                        'startValue' => $params['startValue'],
                        'currentValue' => $params['currentValue'],
                        'endValue' => $params['endValue'],
                        'itemId' => $params['itemId'],
                        'canvasId' => $params['canvasId'],
                        'parent' => $params['parent'] ?? null,
                        'id' => $params['itemId'],
                        'kpi' => $params['kpi'] ?? '',
                        'startDate' => format(value: $params['startDate'], fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime(),
                        'endDate' => format(value: $params['endDate'], fromFormat: FromFormat::UserDateEndOfDay)->isoDateTime(),
                        'setting' => $params['setting'] ?? '',
                        'metricType' => $params['metricType'],
                        'assignedTo' => $params['assignedTo'] ?? '',
                        'milestoneId' => $params['milestoneId'] ?? '',
                    ];

                    if (isset($params['newMilestone']) && $params['newMilestone'] != '') {
                        $params['headline'] = $params['newMilestone'];
                        $params['tags'] = '#ccc';
                        $params['editFrom'] = dtHelper()->userNow()->formatDateForUser();
                        $params['editTo'] = dtHelper()->userNow()->addDays(7)->formatDateForUser();
                        $params['dependentMilestone'] = '';
                        $id = $this->ticketService->quickAddMilestone($params);

                        if ($id !== false) {
                            $canvasItem['milestoneId'] = $id;
                        }
                    }
                    if (isset($params['existingMilestone']) && $params['existingMilestone'] != '') {
                        $canvasItem['milestoneId'] = $params['existingMilestone'];
                    }

                    // Resolves the item's real project from itemId and authorizes EDIT there.
                    $this->goalService->updateGoalItem($canvasItem);

                    $comments = $this->commentsRepo->getComments('goalcanvasitem', $params['itemId']);
                    $this->tpl->assign('numComments', $this->commentsRepo->countComments(
                        'goalcanvasitem',
                        $params['itemId']
                    ));
                    $this->tpl->assign('comments', $comments);

                    $this->tpl->setNotification($this->language->__('notifications.canvas_item_updates'), 'success', 'goal_created');

                    $subject = $this->language->__('email_notifications.canvas_board_edited');
                    $actual_link = BASE_URL.'#/goalcanvas/editCanvasItem/'.(int) $params['itemId'];
                    $message = sprintf(
                        $this->language->__('email_notifications.canvas_item_update_message'),
                        session('userdata.name'),
                        strip_tags($canvasItem['description'])
                    );

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = [
                        'url' => $actual_link,
                        'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
                    ];
                    $notification->entity = $canvasItem;
                    $notification->module = 'goalcanvas';
                    $notification->action = 'updated';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id');
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }

                return Frontcontroller::redirect(BASE_URL.'/goalcanvas/editCanvasItem/'.$params['itemId']);
            } else {

                if (isset($_POST['title']) && ! empty($_POST['title'])) {
                    $canvasItem = [
                        'box' => $params['box'],
                        'author' => session('userdata.id'),
                        'title' => $params['title'],
                        'description' => $params['description'] ?? '',
                        'status' => $params['status'] ?? '',
                        'relates' => '',
                        'startValue' => $params['startValue'],
                        'currentValue' => $params['currentValue'],
                        'endValue' => $params['endValue'],
                        'canvasId' => $params['canvasId'],
                        'parent' => $params['parent'] ?? null,
                        'kpi' => $params['kpi'] ?? '',
                        'startDate' => format(value: $params['startDate'] ?? '', fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime(),
                        'endDate' => format(value: $params['endDate'] ?? '', fromFormat: FromFormat::UserDateEndOfDay)->isoDateTime(),
                        'setting' => $params['setting'] ?? '',
                        'metricType' => $params['metricType'],
                        'assignedTo' => $params['assignedTo'] ?? '',
                    ];
                    // Resolves the target board's real project from canvasId and authorizes CREATE.
                    $id = $this->goalService->createGoalItem($canvasItem);
                    $canvasTypes = $this->canvasRepo->getCanvasTypes();

                    $this->tpl->setNotification($canvasTypes[$params['box']]['title'].' successfully created', 'success', 'goal_item_created');

                    $subject = $this->language->__('email_notifications.canvas_board_item_created');
                    $actual_link = BASE_URL.'#/goalcanvas/editCanvasItem/'.(int) $params['itemId'];
                    $message = sprintf(
                        $this->language->__('email_notifications.canvas_item_created_message'),
                        session('userdata.name'),
                        strip_tags($canvasItem['description'])
                    );

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = [
                        'url' => $actual_link,
                        'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
                    ];

                    $notification->entity = $canvasItem;
                    $notification->module = 'goalcanvas';
                    $notification->action = 'created';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id');
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    $this->tpl->setNotification($this->language->__('notification.element_created'), 'success');
                } else {
                    $id = '';
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }

                return Frontcontroller::redirect(BASE_URL.'/goalcanvas/editCanvasItem/'.$id);
            }
        }

        $this->tpl->assign('canvasTypes', $this->canvasRepo->getCanvasTypes());
        $this->tpl->assign('statusLabels', $this->canvasRepo->getStatusLabels());

        $this->tpl->assign('dataLabels', $this->canvasRepo->getDataLabels());

        if (isset($params['id'])) {
            $canvasItemId = (int) $params['id'];
            $comments = $this->commentsRepo->getComments('goalcanvasitem', $canvasItemId);
            $this->tpl->assign('canvasItem', $this->goalService->getGoalItem($canvasItemId));
        } else {
            $value = [
                'id' => '',
                'box' => $params['box'],
                'author' => session('userdata.id'),
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
                'metricType' => '',
                'assignedTo' => session('userdata.id'),
            ];
            $comments = [];
            $this->tpl->assign('canvasItem', $value);
        }
        $this->tpl->assign('comments', $comments);

        return $this->tpl->displayPartial('goalcanvas.editCanvasItem');
    }
}
