<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

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
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

/**
 * EditCanvasComment controller - handles the comment-focused editing view for canvas items.
 *
 * Replaces the old per-variant Canvas\Controllers\EditCanvasComment subclasses.
 * The canvas type slug comes from the route instead of a class constant.
 */
class EditCanvasComment
{
    private string $canvasSlug;

    private ?CanvasTemplate $template;

    /**
     * __construct - resolve dependencies and determine the canvas slug from request.
     *
     * @param  IncomingRequest  $request  Incoming request
     * @param  Template  $tpl  Template engine
     * @param  Language  $language  Language service
     * @param  TicketRepository  $ticketRepo  Ticket repository
     * @param  CommentRepository  $commentsRepo  Comment repository
     * @param  SprintService  $sprintService  Sprint service
     * @param  TicketService  $ticketService  Ticket service
     * @param  ProjectService  $projectService  Project service
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  BlueprintsService  $blueprintsService  Blueprints service
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function __construct(
        private IncomingRequest $request,
        private Template $tpl,
        private Language $language,
        private TicketRepository $ticketRepo,
        private CommentRepository $commentsRepo,
        private SprintService $sprintService,
        private TicketService $ticketService,
        private ProjectService $projectService,
        private BlueprintsRepository $blueprintsRepo,
        private BlueprintsService $blueprintsService,
        TemplateRegistry $templateRegistry,
    ) {
        $this->canvasSlug = strip_tags((string) ($request->route('canvasSlug') ?? ''));
        $this->template = $templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - handle GET requests for the comment editing view.
     *
     * @param  string|null  $id  Canvas item id from the route
     */
    public function get(?string $id = null): Response
    {
        $data = $this->request->getRequestParams();
        if ($id !== null) {
            $data['id'] = $id;
        }

        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $commentModule = $this->template->getCommentModule();
        $canvasTypes = $this->blueprintsService->getTranslatedBoxes($this->template);
        $statusLabels = $this->blueprintsService->getTranslatedStatusLabels($this->template);
        $relatesLabels = $this->blueprintsService->getTranslatedRelatesLabels($this->template);

        if (isset($data['id'])) {
            // Delete comment
            if (isset($data['delComment']) === true) {
                $commentId = (int) ($data['delComment']);
                $this->commentsRepo->deleteComment($commentId);
                $this->tpl->setNotification(
                    $this->language->__('notifications.comment_deleted'),
                    'success',
                    strtoupper($this->canvasSlug).'canvascomment_deleted'
                );
            }

            $canvasItem = $this->blueprintsRepo->getSingleCanvasItem((int) $data['id']);

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
            if (isset($data['type'])) {
                $type = strip_tags($data['type']);
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
     * @param  string|null  $id  Canvas item id from the route
     */
    public function post(?string $id = null): Response
    {
        $data = $this->request->getRequestParams();
        if ($id !== null) {
            $data['id'] = $id;
        }

        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $commentModule = $this->template->getCommentModule();
        $sessionKey = $this->template->getSessionKey();
        $basePath = '/blueprints/'.$this->canvasSlug;

        if (isset($data['changeItem'])) {
            if (isset($data['itemId']) && $data['itemId'] != '') {
                if (isset($data['description']) && ! empty($data['description'])) {
                    $currentCanvasId = (int) session($sessionKey);

                    $canvasItem = [
                        'box' => $data['box'],
                        'author' => session('userdata.id'),
                        'description' => $data['description'],
                        'status' => $data['status'],
                        'relates' => $data['relates'],
                        'assumptions' => $data['assumptions'],
                        'data' => $data['data'],
                        'conclusion' => $data['conclusion'],
                        'itemId' => $data['itemId'],
                        'id' => $data['itemId'],
                        'canvasId' => $currentCanvasId,
                        'milestoneId' => $data['milestoneId'],
                        'dependentMilstone' => '',
                    ];

                    $this->blueprintsRepo->editCanvasItem($canvasItem);

                    $comments = $this->commentsRepo->getComments($commentModule, $data['itemId']);
                    $this->tpl->assign('numComments', $this->commentsRepo->countComments(
                        $commentModule,
                        $data['itemId']
                    ));
                    $this->tpl->assign('comments', $comments);

                    $this->tpl->setNotification(
                        $this->language->__('notifications.canvas_item_updates'),
                        'success',
                        strtoupper($this->canvasSlug).'canvasitem_updated'
                    );

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = [
                        'url' => BASE_URL.$basePath.'/editCanvasComment/'.(int) $data['itemId'],
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

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/editCanvasComment/'.$data['itemId']);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_element_title'), 'error');
                }
            } else {
                if (isset($data['description']) && ! empty($data['description'])) {
                    $currentCanvasId = (int) session($sessionKey);

                    $canvasItem = [
                        'box' => $data['box'],
                        'author' => session('userdata.id'),
                        'description' => $data['description'],
                        'status' => $data['status'],
                        'relates' => $data['relates'],
                        'assumptions' => $data['assumptions'],
                        'data' => $data['data'],
                        'conclusion' => $data['conclusion'],
                        'canvasId' => $currentCanvasId,
                    ];

                    $id = $this->blueprintsRepo->addCanvasItem($canvasItem);

                    $canvasItem['id'] = $id;

                    $canvasTypes = $this->blueprintsService->getTranslatedBoxes($this->template);

                    $this->tpl->setNotification(
                        ($canvasTypes[$data['box']]['title'] ?? $data['box']).' successfully created',
                        'success',
                        strtoupper($this->canvasSlug).'canvasitem_created'
                    );

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = [
                        'url' => BASE_URL.$basePath.'/editCanvasComment/'.(int) ($data['itemId'] ?? $id),
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

        if (isset($data['comment']) === true) {
            $itemId = (int) ($data['id'] ?? 0);
            $values = [
                'text' => $data['text'],
                'date' => date('Y-m-d H:i:s'),
                'userId' => (session('userdata.id')),
                'moduleId' => $itemId,
                'commentParent' => ($data['father']),
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

        $itemId = (int) ($data['id'] ?? 0);
        $this->tpl->assign('id', $itemId);
        $this->tpl->assign('canvasTypes', $this->blueprintsService->getTranslatedBoxes($this->template));
        $this->tpl->assign('canvasItem', $this->blueprintsRepo->getSingleCanvasItem($itemId));
        $this->tpl->assign('canvasSlug', $this->canvasSlug);

        return $this->tpl->displayPartial('blueprints.canvasComment');
    }
}
