<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
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
class BoardDialog extends Controller
{
    private ProjectService $projectService;

    private BlueprintsRepository $blueprintsRepo;

    private TemplateRegistry $templateRegistry;

    private string $canvasSlug = '';

    private ?CanvasTemplate $template = null;

    /**
     * init - resolve dependencies and determine the canvas slug from request.
     *
     * @param  ProjectService  $projectService  Project service
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function init(
        ProjectService $projectService,
        BlueprintsRepository $blueprintsRepo,
        TemplateRegistry $templateRegistry
    ): void {
        $this->projectService = $projectService;
        $this->blueprintsRepo = $blueprintsRepo;
        $this->templateRegistry = $templateRegistry;

        $this->canvasSlug = strip_tags(request()->route('canvasSlug') ?? ($_GET['canvasSlug'] ?? ''));
        $this->template = $this->templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - display the create/edit board dialog.
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function get(array $params): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $currentCanvasId = '';
        $canvasTitle = '';

        if (isset($params['id'])) {
            $currentCanvasId = (int) $params['id'];
            $singleCanvas = $this->blueprintsRepo->getSingleCanvas($currentCanvasId, $this->template->getDatabaseType());
            $canvasTitle = $singleCanvas[0]['title'] ?? '';
            session([$this->template->getSessionKey() => $currentCanvasId]);
        }

        return $this->renderDialog($currentCanvasId, $canvasTitle);
    }

    /**
     * post - handle create/edit board submissions.
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function post(array $params): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $canvasType = $this->template->getDatabaseType();
        $sessionKey = $this->template->getSessionKey();
        $basePath = '/blueprints/'.$this->canvasSlug;

        $currentCanvasId = isset($params['id']) ? (int) $params['id'] : '';
        $canvasTitle = '';
        if (is_int($currentCanvasId) && $currentCanvasId > 0) {
            $singleCanvas = $this->blueprintsRepo->getSingleCanvas($currentCanvasId, $canvasType);
            $canvasTitle = $singleCanvas[0]['title'] ?? '';
            session([$sessionKey => $currentCanvasId]);
        }

        // Add Canvas
        if (isset($params['newCanvas'])) {
            if (isset($params['canvastitle']) && ! empty($params['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $params['canvastitle'], $canvasType)) {
                    $values = [
                        'title' => $params['canvastitle'],
                        'author' => session('userdata.id'),
                        'projectId' => session('currentProject'),
                    ];
                    $currentCanvasId = $this->blueprintsRepo->addCanvas($values, $canvasType);

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
        if (isset($params['editCanvas']) && is_int($currentCanvasId) && $currentCanvasId > 0) {
            if (isset($params['canvastitle']) && ! empty($params['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $params['canvastitle'], $canvasType)) {
                    $this->blueprintsRepo->updateCanvas(['title' => $params['canvastitle'], 'id' => $currentCanvasId]);

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
