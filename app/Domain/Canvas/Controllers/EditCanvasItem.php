<?php

namespace Leantime\Domain\Canvas\Controllers;

use Illuminate\Support\Str;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * editCanvasItem class - Generic canvas controller / Edit Canvas Item.
 *
 * By-id canvas item access routes through the Blueprints service (scoped to this canvas type,
 * static::CANVAS_NAME.'canvas') so it is authorized against the item's real project; the
 * per-variant repo is used only for label/config reads. The base canvas type ('??canvas') has
 * no data — these controllers are reached via subclasses (e.g. Logicmodelcanvas).
 */
class EditCanvasItem extends Controller
{
    /**
     * Constant that must be redefined
     *
     * @var string
     */
    protected const CANVAS_NAME = '??';

    private TicketService $ticketService;

    private ProjectService $projectService;

    private CommentRepository $commentsRepo;

    private BlueprintsService $blueprintsService;

    private object $canvasRepo;

    /**
     * __construct - constructor
     */
    public function __construct(
        IncomingRequest $incomingRequest,
        Template $tpl,
        Language $language
    ) {
        $this->ticketService = app()->make(TicketService::class);
        $this->projectService = app()->make(ProjectService::class);
        $this->commentsRepo = app()->make(CommentRepository::class);
        $this->blueprintsService = app()->make(BlueprintsService::class);
        $canvasName = Str::studly(static::CANVAS_NAME).'canvas';
        $repoName = app()->getNamespace()."Domain\\$canvasName\\Repositories\\$canvasName";
        $this->canvasRepo = app()->make($repoName);

        parent::__construct($incomingRequest, $tpl, $language);
    }

    /**
     * get - handle get requests
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, entityScoped: true)]
    public function get($params)
    {
        $canvasType = static::CANVAS_NAME.'canvas';
        $commentModule = static::CANVAS_NAME.'canvas'.'item';

        if (isset($params['id'])) {
            // Resolve + VIEW-authorize the item against its real project BEFORE any mutation.
            $canvasItem = $this->blueprintsService->getCanvasItem((int) $params['id'], $canvasType);
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
                    && (string) $comment['module'] === $commentModule
                    && (int) $comment['moduleId'] === (int) $canvasItem['id']) {
                    $this->commentsRepo->deleteComment($commentId);
                    $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success');
                }
            }

            // Delete milestone relationship — an EDIT, authorized by the service.
            if (isset($params['removeMilestone'])) {
                $this->blueprintsService->patchCanvasItem((int) $params['id'], ['milestoneId' => ''], $canvasType);
                $canvasItem = $this->blueprintsService->getCanvasItem((int) $params['id'], $canvasType);
                $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), 'success');
            }

            $comments = $this->commentsRepo->getComments($commentModule, $canvasItem['id']);
            $this->tpl->assign(
                'numComments',
                $this->commentsRepo->countComments($commentModule, $canvasItem['id'])
            );
        } else {
            if (isset($params['type'])) {
                $type = strip_tags($params['type']);
            } else {
                $type = array_key_first($this->canvasRepo->elementLabels);
            }

            $canvasItem = [
                'id' => '',
                'box' => $type,
                'description' => '',
                'status' => array_key_first($this->canvasRepo->getStatusLabels()),
                'relates' => array_key_first($this->canvasRepo->getRelatesLabels()),
                'assumptions' => '',
                'data' => '',
                'conclusion' => '',
                'milestoneHeadline' => '',
                'milestoneId' => '',
            ];

            $comments = [];
        }

        $this->tpl->assign('comments', $comments);

        $allProjectMilestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
        $this->tpl->assign('milestones', $allProjectMilestones);
        $this->tpl->assign('canvasItem', $canvasItem);
        $this->tpl->assign('canvasIcon', $this->canvasRepo->getIcon());
        $this->tpl->assign('relatesLabels', $this->canvasRepo->getRelatesLabels());
        $this->tpl->assign('canvasTypes', $this->canvasRepo->getCanvasTypes());
        $this->tpl->assign('statusLabels', $this->canvasRepo->getStatusLabels());
        $this->tpl->assign('dataLabels', $this->canvasRepo->getDataLabels());
        $this->tpl->assign('currentCanvas', (int) session('current'.strtoupper(static::CANVAS_NAME).'Canvas'));

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas'.'.canvasDialog');
    }

    /**
     * post - handle post requests
     */
    #[RequiresPermission(BlueprintsPermissions::EDIT, entityScoped: true)]
    public function post($params)
    {
        $canvasType = static::CANVAS_NAME.'canvas';

        if (isset($params['changeItem'])) {

            if (isset($params['itemId']) && ! empty($params['itemId'])) {
                if (isset($params['description']) && ! empty($params['description'])) {
                    $currentCanvasId = (int) session('current'.strtoupper(static::CANVAS_NAME).'Canvas');

                    $canvasItem = [
                        'box' => $params['box'],
                        'author' => session('userdata.id'),
                        'description' => $params['description'],
                        'status' => $params['status'],
                        'relates' => $params['relates'],
                        'assumptions' => $params['assumptions'],
                        'data' => $params['data'],
                        'conclusion' => $params['conclusion'],
                        'itemId' => $params['itemId'],
                        'canvasId' => $currentCanvasId,
                        'milestoneId' => $params['milestoneId'],
                        'dependentMilstone' => '',
                        'id' => $params['itemId'],
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
                    $this->blueprintsService->updateCanvasItem($canvasItem, $canvasType);

                    $comments = $this->commentsRepo->getComments(static::CANVAS_NAME.'canvas'.'item', $params['itemId']);
                    $this->tpl->assign('numComments', $this->commentsRepo->countComments(
                        static::CANVAS_NAME.'canvas'.'item',
                        $params['itemId']
                    ));
                    $this->tpl->assign('comments', $comments);

                    $this->tpl->setNotification($this->language->__('notifications.canvas_item_updates'), 'success');

                    $subject = $this->language->__('email_notifications.canvas_board_edited');
                    $actual_link = BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'#/editCanvasItem/'.(int) $params['itemId'];
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
                    $notification->module = static::CANVAS_NAME.'canvas';
                    $notification->action = 'updated';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id');
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    $closeModal = '';
                    if (isset($_POST['submitAction']) && $_POST['submitAction'] == 'closeModal') {
                        $closeModal = '?closeModal=true';
                    }

                    return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'/editCanvasItem/'.$params['itemId'].$closeModal);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            } else {
                if (isset($_POST['description']) && ! empty($_POST['description'])) {
                    $currentCanvasId = (int) session('current'.strtoupper(static::CANVAS_NAME).'Canvas');

                    $canvasItem = [
                        'box' => $params['box'],
                        'author' => session('userdata.id'),
                        'description' => $params['description'],
                        'status' => $params['status'],
                        'relates' => $params['relates'],
                        'assumptions' => $params['assumptions'],
                        'data' => $params['data'],
                        'conclusion' => $params['conclusion'],
                        'canvasId' => $currentCanvasId,
                    ];

                    // Resolves the target board's real project from canvasId and authorizes CREATE.
                    $id = $this->blueprintsService->createCanvasItem($canvasItem, $canvasType);
                    $canvasTypes = $this->canvasRepo->getCanvasTypes();

                    $this->tpl->setNotification($canvasTypes[$params['box']]['title'].' successfully created', 'success', ''.$params['box'].'_item_created');

                    $subject = $this->language->__('email_notifications.canvas_board_item_created');
                    $actual_link = BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'#/editCanvasItem/'.(int) ($params['itemId'] ?? $id);
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
                    $notification->module = static::CANVAS_NAME.'canvas';
                    $notification->action = 'created';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id');
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    $this->tpl->setNotification($this->language->__('notification.element_created'), 'success');

                    $closeModal = '';
                    if (isset($_POST['submitAction']) && $_POST['submitAction'] == 'closeModal') {
                        $closeModal = '?closeModal=true';
                    }

                    return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'/editCanvasItem/'.$id.$closeModal);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }
        }

        if (isset($params['comment']) && isset($params['id'])) {
            $itemId = (int) $params['id'];

            // Only allow commenting on an item the user can view in their project.
            if (! $this->blueprintsService->getCanvasItem($itemId, $canvasType)) {
                return $this->tpl->displayPartial('errors.error404');
            }

            $values = [
                'text' => $params['text'],
                'date' => date('Y-m-d H:i:s'),
                'userId' => (session('userdata.id')),
                'moduleId' => $itemId,
                'commentParent' => ($params['father']),
            ];

            $commentId = $this->commentsRepo->addComment($values, static::CANVAS_NAME.'canvas'.'item');
            $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');
            $values['id'] = $commentId;

            $subject = $this->language->__('email_notifications.canvas_board_comment_created');
            $actual_link = BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'#/editCanvasItem/'.$itemId;
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
            $notification->module = static::CANVAS_NAME.'canvas';
            $notification->action = 'commented';
            $notification->projectId = session('currentProject');
            $notification->subject = $subject;
            $notification->authorId = session('userdata.id');
            $notification->message = $message;

            $this->projectService->notifyProjectUsers($notification);

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas'.'/editCanvasItem/'.$itemId);
        }

        $allProjectMilestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
        $this->tpl->assign('milestones', $allProjectMilestones);
        $this->tpl->assign('canvasTypes', $this->canvasRepo->getCanvasTypes());
        $this->tpl->assign('statusLabels', $this->canvasRepo->getStatusLabels());
        $this->tpl->assign('relatesLabels', $this->canvasRepo->getRelatesLabels());
        $this->tpl->assign('dataLabels', $this->canvasRepo->getDataLabels());
        if (isset($params['id'])) {
            $canvasItemId = (int) $params['id'];
            // VIEW-authorize before re-displaying; false = missing/foreign/unauthorized -> 404.
            $canvasItem = $this->blueprintsService->getCanvasItem($canvasItemId, $canvasType);
            if (! $canvasItem) {
                return $this->tpl->displayPartial('errors.error404');
            }
            $comments = $this->commentsRepo->getComments(static::CANVAS_NAME.'canvas'.'item', $canvasItemId);
            $this->tpl->assign('canvasItem', $canvasItem);
        } else {
            $value = [
                'id' => '',
                'box' => $params['box'],
                'author' => session('userdata.id'),
                'description' => '',
                'status' => array_key_first($this->canvasRepo->getStatusLabels()),
                'relates' => array_key_first($this->canvasRepo->getRelatesLabels()),
                'assumptions' => '',
                'data' => '',
                'conclusion' => '',
                'milestoneHeadline' => '',
                'milestoneId' => '',
            ];
            $comments = [];
            $this->tpl->assign('canvasItem', $value);
        }
        $this->tpl->assign('comments', $comments);
        $this->tpl->assign('currentCanvas', (int) session('current'.strtoupper(static::CANVAS_NAME).'Canvas'));

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas'.'.canvasDialog');
    }

    /**
     * put - handle put requests
     */
    public function put($params) {}

    /**
     * delete - handle delete requests
     */
    public function delete($params) {}
}
