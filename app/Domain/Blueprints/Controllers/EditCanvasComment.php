<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

/**
 * EditCanvasComment controller - handles the comment-focused editing view for canvas items.
 *
 * Replaces the old per-variant Canvas\Controllers\EditCanvasComment subclasses.
 * The canvas type slug comes from a GET parameter instead of a class constant.
 */
class EditCanvasComment extends Controller
{
    private TicketRepository $ticketRepo;

    private CommentRepository $commentsRepo;

    private SprintService $sprintService;

    private TicketService $ticketService;

    private ProjectService $projectService;

    private BlueprintsRepository $blueprintsRepo;

    private BlueprintsService $blueprintsService;

    private TemplateRegistry $templateRegistry;

    private string $canvasSlug = '';

    private ?CanvasTemplate $template = null;

    /**
     * init - resolve dependencies and determine the canvas slug from request.
     *
     * @param  TicketRepository  $ticketRepo  Ticket repository
     * @param  CommentRepository  $commentsRepo  Comment repository
     * @param  SprintService  $sprintService  Sprint service
     * @param  TicketService  $ticketService  Ticket service
     * @param  ProjectService  $projectService  Project service
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  BlueprintsService  $blueprintsService  Blueprints service
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function init(
        TicketRepository $ticketRepo,
        CommentRepository $commentsRepo,
        SprintService $sprintService,
        TicketService $ticketService,
        ProjectService $projectService,
        BlueprintsRepository $blueprintsRepo,
        BlueprintsService $blueprintsService,
        TemplateRegistry $templateRegistry
    ): void {
        $this->ticketRepo = $ticketRepo;
        $this->commentsRepo = $commentsRepo;
        $this->sprintService = $sprintService;
        $this->ticketService = $ticketService;
        $this->projectService = $projectService;
        $this->blueprintsRepo = $blueprintsRepo;
        $this->blueprintsService = $blueprintsService;
        $this->templateRegistry = $templateRegistry;

        $this->canvasSlug = strip_tags(request()->route('canvasSlug') ?? ($_GET['canvasSlug'] ?? ''));
        $this->template = $this->templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - handle GET requests for the comment editing view.
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
            if (isset($params['delComment']) === true) {
                $commentId = (int) ($params['delComment']);
                $this->commentsRepo->deleteComment($commentId);
                $this->tpl->setNotification(
                    $this->language->__('notifications.comment_deleted'),
                    'success',
                    strtoupper($this->canvasSlug).'canvascomment_deleted'
                );
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
        $this->tpl->assign('canvasTypes', $canvasTypes);
        $this->tpl->assign('canvasItem', $canvasItem);
        $this->tpl->assign('canvasSlug', $this->canvasSlug);

        return $this->tpl->displayPartial('blueprints.canvasComment');
    }

    /**
     * post - handle POST requests for updating canvas items and adding comments.
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
            if (isset($params['itemId']) && $params['itemId'] != '') {
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
                        'id' => $params['itemId'],
                        'canvasId' => $currentCanvasId,
                        'milestoneId' => $params['milestoneId'],
                        'dependentMilstone' => '',
                    ];

                    $this->blueprintsRepo->editCanvasItem($canvasItem);

                    $comments = $this->commentsRepo->getComments($commentModule, $params['itemId']);
                    $this->tpl->assign('numComments', $this->commentsRepo->countComments(
                        $commentModule,
                        $params['itemId']
                    ));
                    $this->tpl->assign('comments', $comments);

                    $this->tpl->setNotification(
                        $this->language->__('notifications.canvas_item_updates'),
                        'success',
                        strtoupper($this->canvasSlug).'canvasitem_updated'
                    );

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = [
                        'url' => BASE_URL.$basePath.'/editCanvasComment/'.(int) $params['itemId'],
                        'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
                    ];
                    $notification->entity = $canvasItem;
                    $notification->module = $this->canvasSlug.'canvas';
                    $notification->action = 'updated';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $this->language->__('email_notifications.canvas_board_edited');
                    $notification->authorId = session('userdata.id');
                    $notification->message = sprintf(
                        $this->language->__('email_notifications.canvas_item_update_message'),
                        session('userdata.name'),
                        $canvasItem['description']
                    );

                    $this->projectService->notifyProjectUsers($notification);

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/editCanvasComment/'.$params['itemId']);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_element_title'), 'error');
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

                    $canvasItem['id'] = $id;

                    $canvasTypes = $this->blueprintsService->getTranslatedBoxes($this->template);

                    $this->tpl->setNotification(
                        ($canvasTypes[$params['box']]['title'] ?? $params['box']).' successfully created',
                        'success',
                        strtoupper($this->canvasSlug).'canvasitem_created'
                    );

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = [
                        'url' => BASE_URL.$basePath.'/editCanvasComment/'.(int) ($params['itemId'] ?? $id),
                        'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
                    ];
                    $notification->entity = $canvasItem;
                    $notification->module = $this->canvasSlug.'canvas';
                    $notification->action = 'created';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $this->language->__('email_notifications.canvas_board_item_created');
                    $notification->authorId = session('userdata.id');
                    $notification->message = sprintf(
                        $this->language->__('email_notifications.canvas_item_created_message'),
                        session('userdata.name'),
                        $canvasItem['description']
                    );

                    $this->projectService->notifyProjectUsers($notification);

                    $this->tpl->setNotification(
                        $this->language->__('notification.element_created'),
                        'success',
                        strtoupper($this->canvasSlug).'canvasitem_created'
                    );

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/editCanvasComment/'.$id);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_element_title'), 'error');
                }
            }
        }

        if (isset($params['comment']) === true) {
            $itemId = (int) ($_GET['id'] ?? 0);
            $values = [
                'text' => $params['text'],
                'date' => date('Y-m-d H:i:s'),
                'userId' => (session('userdata.id')),
                'moduleId' => $itemId,
                'commentParent' => ($params['father']),
            ];

            $this->commentsRepo->addComment($values, $commentModule);
            $this->tpl->setNotification(
                $this->language->__('notifications.comment_create_success'),
                'success',
                strtoupper($this->canvasSlug).'canvasitemcomment_created'
            );

            $notification = app()->make(NotificationModel::class);
            $notification->url = [
                'url' => BASE_URL.$basePath.'/editCanvasComment/'.$itemId,
                'text' => $this->language->__('email_notifications.canvas_item_update_cta'),
            ];
            $notification->entity = $values;
            $notification->module = $this->canvasSlug.'canvas';
            $notification->action = 'commented';
            $notification->projectId = session('currentProject');
            $notification->subject = $this->language->__('email_notifications.canvas_board_comment_created');
            $notification->authorId = session('userdata.id');
            $notification->message = sprintf(
                $this->language->__('email_notifications.canvas_item__comment_created_message'),
                session('userdata.name')
            );

            $this->projectService->notifyProjectUsers($notification);

            return Frontcontroller::redirect(BASE_URL.$basePath.'/editCanvasComment/'.$itemId);
        }

        $itemId = (int) ($_GET['id'] ?? 0);
        $this->tpl->assign('id', $itemId);
        $this->tpl->assign('canvasTypes', $this->blueprintsService->getTranslatedBoxes($this->template));
        $this->tpl->assign('canvasItem', $this->blueprintsRepo->getSingleCanvasItem($itemId));
        $this->tpl->assign('canvasSlug', $this->canvasSlug);

        return $this->tpl->displayPartial('blueprints.canvasComment');
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
