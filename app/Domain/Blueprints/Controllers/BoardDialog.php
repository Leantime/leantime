<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Core\UI\Template;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * BoardDialog controller - handles the create/edit board dialog for blueprints.
 *
 * Replaces the old per-variant Canvas\Controllers\BoardDialog subclasses.
 * The canvas type slug comes from a GET parameter instead of a class constant.
 */
class BoardDialog
{
    private string $canvasSlug;

    private ?CanvasTemplate $template;

    /**
     * __construct - resolve dependencies and determine the canvas slug from request.
     *
     * @param  IncomingRequest  $request  Incoming request
     * @param  Template  $tpl  Template engine
     * @param  Language  $language  Language service
     * @param  ProjectService  $projectService  Project service
     * @param  BlueprintsService  $blueprintsService  Blueprints service (project-authorized board CRUD)
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository (currentProject-scoped existence check)
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function __construct(
        private IncomingRequest $request,
        private Template $tpl,
        private Language $language,
        private ProjectService $projectService,
        private BlueprintsService $blueprintsService,
        private BlueprintsRepository $blueprintsRepo,
        TemplateRegistry $templateRegistry,
    ) {
        $this->canvasSlug = strip_tags((string) ($request->route('canvasSlug') ?? ''));
        $this->template = $templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - display the create/edit board dialog.
     *
     * @param  string|null  $id  Current board id
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, entityScoped: true)]
    public function get(?string $id = null): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $currentCanvasId = '';
        $canvasTitle = '';

        if ($id !== null) {
            // getBoard authorizes VIEW against the board's real project; false = missing /
            // foreign / unauthorized, in which case we neither expose the title nor switch
            // the active board (no session poisoning with a foreign id).
            $singleCanvas = $this->blueprintsService->getBoard((int) $id, $this->template->getDatabaseType());
            if ($singleCanvas !== false) {
                $currentCanvasId = (int) $id;
                $canvasTitle = $singleCanvas[0]['title'] ?? '';
                session([$this->template->getSessionKey() => $currentCanvasId]);
            }
        }

        return $this->renderDialog($currentCanvasId, $canvasTitle);
    }

    /**
     * post - handle create/edit board submissions.
     *
     * @param  string|null  $id  Current board id
     */
    #[RequiresPermission(BlueprintsPermissions::EDIT, entityScoped: true)]
    public function post(?string $id = null): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $canvasType = $this->template->getDatabaseType();
        $sessionKey = $this->template->getSessionKey();
        $basePath = '/blueprints/'.$this->canvasSlug;

        $currentCanvasId = ($id !== null && $id !== '') ? (int) $id : '';
        $canvasTitle = '';
        if (is_int($currentCanvasId) && $currentCanvasId > 0) {
            $singleCanvas = $this->blueprintsService->getBoard($currentCanvasId, $canvasType);
            if ($singleCanvas !== false) {
                $canvasTitle = $singleCanvas[0]['title'] ?? '';
                session([$sessionKey => $currentCanvasId]);
            }
        }

        // Add Canvas
        if ($this->request->has('newCanvas')) {
            if ($this->request->has('canvastitle') && ! empty($this->request->input('canvastitle'))) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $this->request->input('canvastitle'), $canvasType)) {
                    $values = [
                        'title' => $this->request->input('canvastitle'),
                        'author' => session('userdata.id'),
                        'projectId' => session('currentProject'),
                    ];
                    // createBoard authorizes CREATE against the target (current) project.
                    $currentCanvasId = $this->blueprintsService->createBoard($values, $canvasType);

                    $mailer = app()->make(MailerCore::class);
                    $users = $this->projectService->getUsersToNotify(session('currentProject'));

                    $mailer->setSubject($this->language->__('notification.board_created'));

                    $message = sprintf(
                        $this->language->__('email_notifications.canvas_created_message'),
                        session('userdata.name'),
                        "<a href='".CURRENT_URL."'>".strip_tags($values['title']).'</a>'
                    );
                    $mailer->setHtml($message);

                    $queue = app()->make(QueueRepository::class);
                    $queue->queueMessageToUsers(
                        $users,
                        $message,
                        $this->language->__('notification.board_created'),
                        session('currentProject')
                    );

                    $this->tpl->setNotification(
                        $this->language->__('notification.board_created'),
                        'success',
                        $this->canvasSlug.'board_created'
                    );

                    session([$sessionKey => $currentCanvasId]);

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/boardDialog/'.$currentCanvasId);
                }

                $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        // Edit Canvas
        if ($this->request->has('editCanvas') && is_int($currentCanvasId) && $currentCanvasId > 0) {
            if ($this->request->has('canvastitle') && ! empty($this->request->input('canvastitle'))) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $this->request->input('canvastitle'), $canvasType)) {
                    // renameBoard authorizes EDIT against the board's real project.
                    $this->blueprintsService->renameBoard($currentCanvasId, $this->request->input('canvastitle'), $canvasType);

                    $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/boardDialog/'.$currentCanvasId);
                }

                $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        return $this->renderDialog($currentCanvasId, $canvasTitle);
    }

    /**
     * renderDialog - assign shared template variables and render the board dialog.
     *
     * @param  int|string  $currentCanvasId  Current board id (empty string when creating)
     * @param  string  $canvasTitle  Current board title
     */
    private function renderDialog(int|string $currentCanvasId, string $canvasTitle): Response
    {
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasName', $this->canvasSlug);
        $this->tpl->assign('canvasSlug', $this->canvasSlug);
        $this->tpl->assign('canvasTitle', $canvasTitle);
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));

        return $this->tpl->displayPartial('blueprints.boardDialog');
    }
}
