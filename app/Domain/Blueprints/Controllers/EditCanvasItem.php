<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
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
 * Native Laravel controller: route-bound actions, the {canvasSlug}/{id} path segments
 * arrive via the route (canvasSlug resolved in the constructor, id as a typed action arg),
 * and request input is read from the injected IncomingRequest instead of the legacy
 * merged-$params argument and superglobals.
 *
 * All by-id item access goes through the Blueprints service, which authorizes against the
 * item's real project — the controller never reads/writes canvas items via the repository.
 */
class EditCanvasItem
{
    private string $canvasSlug;

    private ?CanvasTemplate $template;

    /**
     * __construct - resolve dependencies and the canvas template for the requested slug.
     *
     * @param  IncomingRequest  $request  Incoming HTTP request
     * @param  Template  $tpl  Template engine
     * @param  Language  $language  Language service
     * @param  TicketService  $ticketService  Ticket service
     * @param  ProjectService  $projectService  Project service
     * @param  CommentRepository  $commentsRepo  Comments repository
     * @param  BlueprintsService  $blueprintsService  Blueprints service (project-authorized item CRUD)
     * @param  TemplateRegistry  $templateRegistry  Canvas template registry
     */
    public function __construct(
        private IncomingRequest $request,
        private Template $tpl,
        private Language $language,
        private TicketService $ticketService,
        private ProjectService $projectService,
        private CommentRepository $commentsRepo,
        private BlueprintsService $blueprintsService,
        TemplateRegistry $templateRegistry,
    ) {
        $this->canvasSlug = strip_tags((string) ($request->route('canvasSlug') ?? ''));
        $this->template = $templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - handle GET requests for viewing/editing a canvas item.
     *
     * @param  string|null  $id  Canvas item id
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, entityScoped: true)]
    public function get(?string $id = null): Response
    {
        $data = $this->request->getRequestParams();
        if ($id !== null) {
            $data['id'] = $id;
        }

        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $canvasType = $this->template->getDatabaseType();
        $commentModule = $this->template->getCommentModule();
        $canvasTypes = $this->blueprintsService->getTranslatedBoxes($this->template);
        $statusLabels = $this->blueprintsService->getTranslatedStatusLabels($this->template);
        $relatesLabels = $this->blueprintsService->getTranslatedRelatesLabels($this->template);

        if (isset($data['id'])) {
            // Resolve + VIEW-authorize the item against its real project BEFORE any mutation.
            // false = missing / foreign project / unauthorized (indistinguishable -> no oracle).
            $canvasItem = $this->blueprintsService->getCanvasItem((int) $data['id'], $canvasType);
            if (! $canvasItem) {
                return $this->tpl->displayPartial('errors.error404');
            }

            // Delete comment — ONLY when it actually belongs to THIS gated item (same module +
            // moduleId). The item being viewable is not enough: deleteComment() filters on the
            // comment id alone, so without this bind any global comment id (a comment on another
            // item, canvas type, or project — one shared id sequence) could be deleted.
            if (isset($data['delComment'])) {
                $commentId = (int) ($data['delComment']);
                $comment = $this->commentsRepo->getComment($commentId);
                if ($comment !== false
                    && (string) $comment['module'] === $commentModule
                    && (int) $comment['moduleId'] === (int) $canvasItem['id']) {
                    $this->commentsRepo->deleteComment($commentId);
                    $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success');
                }
            }

            // Delete milestone relationship — an EDIT, authorized by the service against the
            // item's project (a view-only user is denied here).
            if (isset($data['removeMilestone'])) {
                $this->blueprintsService->patchCanvasItem((int) $data['id'], ['milestoneId' => ''], $canvasType);
                $canvasItem = $this->blueprintsService->getCanvasItem((int) $data['id'], $canvasType);
                $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), 'success');
            }

            $comments = $this->commentsRepo->getComments($commentModule, $canvasItem['id']);
            $this->tpl->assign(
                'numComments',
                $this->commentsRepo->countComments($commentModule, $canvasItem['id'])
            );
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
     * @param  string|null  $id  Canvas item id
     */
    #[RequiresPermission(BlueprintsPermissions::EDIT, entityScoped: true)]
    public function post(?string $id = null): Response
    {
        $data = $this->request->getRequestParams();
        if ($id !== null) {
            $data['id'] = $id;
        }

        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $canvasType = $this->template->getDatabaseType();
        $commentModule = $this->template->getCommentModule();
        $sessionKey = $this->template->getSessionKey();
        $basePath = '/blueprints/'.$this->canvasSlug;

        if (isset($data['changeItem'])) {
            if (isset($data['itemId']) && ! empty($data['itemId'])) {
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
                        'canvasId' => $currentCanvasId,
                        'milestoneId' => $data['milestoneId'],
                        'dependentMilstone' => '',
                        'id' => $data['itemId'],
                    ];

                    if (isset($data['newMilestone']) && $data['newMilestone'] != '') {
                        $data['headline'] = $data['newMilestone'];
                        $data['tags'] = '#ccc';
                        $data['editFrom'] = dtHelper()->userNow()->formatDateForUser();
                        $data['editTo'] = dtHelper()->userNow()->addDays(7)->formatDateForUser();
                        $data['dependentMilestone'] = '';
                        $id = $this->ticketService->quickAddMilestone($data);

                        if ($id !== false) {
                            $canvasItem['milestoneId'] = $id;
                        }
                    }
                    if (isset($data['existingMilestone']) && $data['existingMilestone'] != '') {
                        $canvasItem['milestoneId'] = $data['existingMilestone'];
                    }

                    // Resolves the item's real project from itemId and authorizes EDIT there;
                    // the payload's canvasId can't relocate the item across projects.
                    $this->blueprintsService->updateCanvasItem($canvasItem, $canvasType);

                    $comments = $this->commentsRepo->getComments($commentModule, $data['itemId']);
                    $this->tpl->assign('numComments', $this->commentsRepo->countComments(
                        $commentModule,
                        $data['itemId']
                    ));
                    $this->tpl->assign('comments', $comments);

                    $this->tpl->setNotification($this->language->__('notifications.canvas_item_updates'), 'success');

                    $subject = $this->language->__('email_notifications.canvas_board_edited');
                    $actualLink = BASE_URL.$basePath.'#/editCanvasItem/'.(int) $data['itemId'];
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
                    $notification->module = $this->canvasSlug.'canvas';
                    $notification->action = 'updated';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id');
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    $closeModal = '';
                    if (isset($data['submitAction']) && $data['submitAction'] == 'closeModal') {
                        $closeModal = '?closeModal=true';
                    }

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/editCanvasItem/'.$data['itemId'].$closeModal);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
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

                    // Resolves the TARGET board's real project from canvasId and authorizes
                    // CREATE there (the board must exist and belong to a project the user can
                    // create in) before inserting.
                    $id = $this->blueprintsService->createCanvasItem($canvasItem, $canvasType);
                    $canvasTypes = $this->blueprintsService->getTranslatedBoxes($this->template);

                    $this->tpl->setNotification(
                        $canvasTypes[$data['box']]['title'].' successfully created',
                        'success',
                        ''.$data['box'].'_item_created'
                    );

                    $subject = $this->language->__('email_notifications.canvas_board_item_created');
                    $actualLink = BASE_URL.$basePath.'#/editCanvasItem/'.(int) ($data['itemId'] ?? $id);
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
                    $notification->module = $this->canvasSlug.'canvas';
                    $notification->action = 'created';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id');
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    $this->tpl->setNotification($this->language->__('notification.element_created'), 'success');

                    $closeModal = '';
                    if (isset($data['submitAction']) && $data['submitAction'] == 'closeModal') {
                        $closeModal = '?closeModal=true';
                    }

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/editCanvasItem/'.$id.$closeModal);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }
        }

        if (isset($data['comment']) && isset($data['id'])) {
            $itemId = (int) $data['id'];

            // Only allow commenting on an item the user can view in their project.
            if (! $this->blueprintsService->getCanvasItem($itemId, $canvasType)) {
                return $this->tpl->displayPartial('errors.error404');
            }

            $values = [
                'text' => $data['text'],
                'date' => date('Y-m-d H:i:s'),
                'userId' => (session('userdata.id')),
                'moduleId' => $itemId,
                'commentParent' => ($data['father']),
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
            $notification->module = $this->canvasSlug.'canvas';
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
        if (isset($data['id'])) {
            $canvasItemId = (int) $data['id'];
            $comments = $this->commentsRepo->getComments($commentModule, $canvasItemId);
            $this->tpl->assign('canvasItem', $this->blueprintsService->getCanvasItem($canvasItemId, $canvasType));
        } else {
            $value = [
                'id' => '',
                'box' => $data['box'],
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
}
