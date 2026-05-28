<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

/**
 * EditCanvasItem controller - handles viewing and editing a single canvas item.
 *
 * Replaces the old per-variant Canvas\Controllers\EditCanvasItem subclasses.
 * The canvas type slug comes from a GET parameter instead of a class constant.
 */
class EditCanvasItem extends Controller
{
    private TicketService $ticketService;

    private ProjectService $projectService;

    private CommentRepository $commentsRepo;

    private BlueprintsRepository $blueprintsRepo;

    private BlueprintsService $blueprintsService;

    private TemplateRegistry $templateRegistry;

    private string $canvasSlug = '';

    private ?CanvasTemplate $template = null;

    /**
     * __construct - resolve dependencies that need to be available before init().
     *
     * @param  IncomingRequest  $incomingRequest  Incoming HTTP request
     * @param  Template  $tpl  Template engine
     * @param  Language  $language  Language service
     */
    public function __construct(
        IncomingRequest $incomingRequest,
        Template $tpl,
        Language $language
    ) {
        $this->ticketService = app()->make(TicketService::class);
        $this->projectService = app()->make(ProjectService::class);
        $this->commentsRepo = app()->make(CommentRepository::class);
        $this->blueprintsRepo = app()->make(BlueprintsRepository::class);
        $this->blueprintsService = app()->make(BlueprintsService::class);
        $this->templateRegistry = app()->make(TemplateRegistry::class);

        $this->canvasSlug = strip_tags(request()->route('canvasSlug') ?? ($_GET['canvasSlug'] ?? ''));
        $this->template = $this->templateRegistry->get($this->canvasSlug);

        parent::__construct($incomingRequest, $tpl, $language);
    }

    /**
     * get - handle GET requests for viewing/editing a canvas item.
     *
     * @param  array<string, mixed>  $params  Request parameters
     * @return Response|void
     */
    public function get(array $params)
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $commentModule = $this->template->getCommentModule();
        $canvasTypes = $this->blueprintsService->getTranslatedBoxes($this->template);
        $statusLabels = $this->blueprintsService->getTranslatedStatusLabels($this->template);
        $relatesLabels = $this->blueprintsService->getTranslatedRelatesLabels($this->template);

        if (isset($params['id'])) {
            // Delete comment
            if (isset($params['delComment'])) {
                $commentId = (int) ($params['delComment']);
                $this->commentsRepo->deleteComment($commentId);
                $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success');
            }

            // Delete milestone relationship
            if (isset($params['removeMilestone'])) {
                $this->blueprintsRepo->patchCanvasItem((int) $params['id'], ['milestoneId' => '']);
                $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), 'success');
            }

            $canvasItem = $this->blueprintsRepo->getSingleCanvasItem((int) $params['id']);

            if ($canvasItem) {
                $comments = $this->commentsRepo->getComments($commentModule, $canvasItem['id']);
                $this->tpl->assign(
                    'numComments',
                    $this->commentsRepo->countComments($commentModule, $canvasItem['id'])
                );
            } else {
                return $this->tpl->displayPartial('errors.error404');
            }
        } else {
            if (isset($params['type'])) {
                $type = strip_tags($params['type']);
            } else {
                $type = array_key_first($canvasTypes);
            }

            $canvasItem = [
                'id' => '',
                'box' => $type,
                'description' => '',
                'status' => array_key_first($statusLabels),
                'relates' => array_key_first($relatesLabels),
                'assumptions' => '',
                'data' => '',
                'conclusion' => '',
                'milestoneHeadline' => '',
                'milestoneId' => '',
            ];

            $comments = [];
        }

        $this->tpl->assign('comments', $comments);

        $allProjectMilestones = $this->ticketService->getAllMilestones([
            'sprint' => '',
            'type' => 'milestone',
            'currentProject' => session('currentProject'),
        ]);
        $this->tpl->assign('milestones', $allProjectMilestones);
        $this->tpl->assign('canvasItem', $canvasItem);
        $this->tpl->assign('canvasSlug', $this->canvasSlug);
        $this->tpl->assign('canvasIcon', $this->template->icon);
        $this->tpl->assign('relatesLabels', $relatesLabels);
        $this->tpl->assign('canvasTypes', $canvasTypes);
        $this->tpl->assign('statusLabels', $statusLabels);
        $this->tpl->assign('dataLabels', $this->blueprintsService->getTranslatedDataLabels($this->template));

        return $this->tpl->displayPartial('blueprints.canvasDialog');
    }

    /**
     * post - handle POST requests for creating/updating canvas items and comments.
     *
     * @param  array<string, mixed>  $params  Request parameters
     * @return Response|void
     */
    public function post(array $params)
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $commentModule = $this->template->getCommentModule();
        $sessionKey = $this->template->getSessionKey();
        $basePath = '/blueprints/'.$this->canvasSlug;

        if (isset($params['changeItem'])) {
            if (isset($params['itemId']) && ! empty($params['itemId'])) {
                if (isset($params['description']) && ! empty($params['description'])) {
                    $currentCanvasId = (int) session($sessionKey);

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

                    $this->blueprintsRepo->editCanvasItem($canvasItem);

                    $comments = $this->commentsRepo->getComments($commentModule, $params['itemId']);
                    $this->tpl->assign('numComments', $this->commentsRepo->countComments(
                        $commentModule,
                        $params['itemId']
                    ));
                    $this->tpl->assign('comments', $comments);

                    $this->tpl->setNotification($this->language->__('notifications.canvas_item_updates'), 'success');

                    $subject = $this->language->__('email_notifications.canvas_board_edited');
                    $actualLink = BASE_URL.$basePath.'#/editCanvasItem/'.(int) $params['itemId'];
                    $message = sprintf(
                        $this->language->__('email_notifications.canvas_item_update_message'),
                        session('userdata.name'),
                        strip_tags($canvasItem['description'])
                    );

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = [
                        'url' => $actualLink,
                        'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
                    ];
                    $notification->entity = $canvasItem;
                    $notification->module = 'blueprints';
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

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/editCanvasItem/'.$params['itemId'].$closeModal);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            } else {
                if (isset($_POST['description']) && ! empty($_POST['description'])) {
                    $currentCanvasId = (int) session($sessionKey);

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

                    $id = $this->blueprintsRepo->addCanvasItem($canvasItem);
                    $canvasTypes = $this->blueprintsService->getTranslatedBoxes($this->template);

                    $this->tpl->setNotification(
                        $canvasTypes[$params['box']]['title'].' successfully created',
                        'success',
                        ''.$params['box'].'_item_created'
                    );

                    $subject = $this->language->__('email_notifications.canvas_board_item_created');
                    $actualLink = BASE_URL.$basePath.'#/editCanvasItem/'.(int) ($params['itemId'] ?? $id);
                    $message = sprintf(
                        $this->language->__('email_notifications.canvas_item_created_message'),
                        session('userdata.name'),
                        strip_tags($canvasItem['description'])
                    );

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = [
                        'url' => $actualLink,
                        'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
                    ];
                    $notification->entity = $canvasItem;
                    $notification->module = 'blueprints';
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

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/editCanvasItem/'.$id.$closeModal);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }
        }

        if (isset($params['comment']) && isset($params['id'])) {
            $itemId = (int) $params['id'];
            $values = [
                'text' => $params['text'],
                'date' => date('Y-m-d H:i:s'),
                'userId' => (session('userdata.id')),
                'moduleId' => $itemId,
                'commentParent' => ($params['father']),
            ];

            $commentId = $this->commentsRepo->addComment($values, $commentModule);
            $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');
            $values['id'] = $commentId;

            $subject = $this->language->__('email_notifications.canvas_board_comment_created');
            $actualLink = BASE_URL.$basePath.'#/editCanvasItem/'.$itemId;
            $message = sprintf(
                $this->language->__('email_notifications.canvas_item__comment_created_message'),
                session('userdata.name')
            );

            $notification = app()->make(NotificationModel::class);
            $notification->url = [
                'url' => $actualLink,
                'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
            ];
            $notification->entity = $values;
            $notification->module = 'blueprints';
            $notification->action = 'commented';
            $notification->projectId = session('currentProject');
            $notification->subject = $subject;
            $notification->authorId = session('userdata.id');
            $notification->message = $message;

            $this->projectService->notifyProjectUsers($notification);

            return Frontcontroller::redirect(BASE_URL.$basePath.'/editCanvasItem/'.$itemId);
        }

        $statusLabels = $this->blueprintsService->getTranslatedStatusLabels($this->template);
        $relatesLabels = $this->blueprintsService->getTranslatedRelatesLabels($this->template);

        $allProjectMilestones = $this->ticketService->getAllMilestones([
            'sprint' => '',
            'type' => 'milestone',
            'currentProject' => session('currentProject'),
        ]);
        $this->tpl->assign('milestones', $allProjectMilestones);
        $this->tpl->assign('canvasTypes', $this->blueprintsService->getTranslatedBoxes($this->template));
        $this->tpl->assign('statusLabels', $statusLabels);
        $this->tpl->assign('relatesLabels', $relatesLabels);
        $this->tpl->assign('dataLabels', $this->blueprintsService->getTranslatedDataLabels($this->template));
        if (isset($params['id'])) {
            $canvasItemId = (int) $params['id'];
            $comments = $this->commentsRepo->getComments($commentModule, $canvasItemId);
            $this->tpl->assign('canvasItem', $this->blueprintsRepo->getSingleCanvasItem($canvasItemId));
        } else {
            $value = [
                'id' => '',
                'box' => $params['box'],
                'author' => session('userdata.id'),
                'description' => '',
                'status' => array_key_first($statusLabels),
                'relates' => array_key_first($relatesLabels),
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
        $this->tpl->assign('canvasSlug', $this->canvasSlug);

        return $this->tpl->displayPartial('blueprints.canvasDialog');
    }

    /**
     * put - handle PUT requests.
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function put(array $params): void {}

    /**
     * delete - handle DELETE requests.
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function delete(array $params): void {}
}
