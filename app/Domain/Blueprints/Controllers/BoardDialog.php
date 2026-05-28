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
     * run - display the board dialog and handle create/edit actions.
     *
     * @return Response|void
     */
    public function run()
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $canvasType = $this->template->getDatabaseType();
        $sessionKey = $this->template->getSessionKey();
        $basePath = '/blueprints/'.$this->canvasSlug;

        $allCanvas = $this->blueprintsRepo->getAllCanvas(session('currentProject'), $canvasType);
        $currentCanvasId = '';
        $canvasTitle = '';

        if (isset($_GET['id']) === true) {
            $currentCanvasId = (int) $_GET['id'];
            $singleCanvas = $this->blueprintsRepo->getSingleCanvas($currentCanvasId, $canvasType);
            $canvasTitle = $singleCanvas[0]['title'] ?? '';
            session([$sessionKey => $currentCanvasId]);
        }

        // Add Canvas
        if (isset($_POST['newCanvas'])) {
            if (isset($_POST['canvastitle']) && ! empty($_POST['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $_POST['canvastitle'], $canvasType)) {
                    $values = [
                        'title' => $_POST['canvastitle'],
                        'author' => session('userdata.id'),
                        'projectId' => session('currentProject'),
                    ];
                    $currentCanvasId = $this->blueprintsRepo->addCanvas($values, $canvasType);
                    $allCanvas = $this->blueprintsRepo->getAllCanvas(session('currentProject'), $canvasType);

                    $mailer = app()->make(MailerCore::class);
                    $this->projectService = app()->make(ProjectService::class);
                    $users = $this->projectService->getUsersToNotify(session('currentProject'));

                    $mailer->setSubject($this->language->__('notification.board_created'));

                    $actualLink = CURRENT_URL;
                    $message = sprintf(
                        $this->language->__('email_notifications.canvas_created_message'),
                        session('userdata.name'),
                        "<a href='".$actualLink."'>".strip_tags($values['title']).'</a>'
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
                } else {
                    $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        // Edit Canvas
        if (isset($_POST['editCanvas']) && $currentCanvasId > 0) {
            if (isset($_POST['canvastitle']) && ! empty($_POST['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $_POST['canvastitle'], $canvasType)) {
                    $values = ['title' => $_POST['canvastitle'], 'id' => $currentCanvasId];
                    $currentCanvasId = $this->blueprintsRepo->updateCanvas($values);

                    $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');

                    return Frontcontroller::redirect(BASE_URL.$basePath.'/boardDialog/'.$values['id']);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasName', $this->canvasSlug);
        $this->tpl->assign('canvasSlug', $this->canvasSlug);
        $this->tpl->assign('canvasTitle', $canvasTitle);
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));

        if (! isset($_GET['raw'])) {
            return $this->tpl->displayPartial('blueprints.boardDialog');
        }
    }
}
