<?php

namespace Leantime\Domain\Canvas\Controllers;

use Illuminate\Support\Str;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles canvas board create/edit dialog.
 */
class BoardDialog extends Controller
{
    /**
     * Constant that must be redefined by subclasses.
     */
    protected const CANVAS_NAME = '??';

    private ProjectService $projectService;

    private BlueprintsService $blueprintsService;

    private object $canvasRepo;

    /**
     * Initializes dependencies.
     */
    public function init(ProjectService $projectService): void
    {
        $this->projectService = $projectService;
        $this->blueprintsService = app()->make(BlueprintsService::class);
        $canvasName = Str::studly(static::CANVAS_NAME).'canvas';
        $repoName = app()->getNamespace()."Domain\\$canvasName\\Repositories\\$canvasName";
        $this->canvasRepo = app()->make($repoName);
    }

    /**
     * Displays the board dialog form.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, entityScoped: true)]
    public function get(array $params): Response
    {
        $currentCanvasId = '';
        $canvasTitle = '';

        if (isset($params['id'])) {
            // getBoard authorizes VIEW against the board's real project; false = missing/foreign/
            // unauthorized — don't expose the title or switch the active board (no session poison).
            $singleCanvas = $this->blueprintsService->getBoard((int) $params['id'], static::CANVAS_NAME.'canvas');
            if ($singleCanvas !== false) {
                $currentCanvasId = (int) $params['id'];
                $canvasTitle = $singleCanvas[0]['title'] ?? '';
                session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);
            }
        }

        $this->assignTemplateVars($currentCanvasId, $canvasTitle);

        if (! isset($_GET['raw'])) {
            return $this->tpl->displayPartial('canvas.boardDialog');
        }

        return new Response;
    }

    /**
     * Handles board creation and editing.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(BlueprintsPermissions::EDIT, entityScoped: true)]
    public function post(array $params): Response
    {
        $currentCanvasId = '';
        $canvasTitle = '';

        if (isset($params['id'])) {
            $singleCanvas = $this->blueprintsService->getBoard((int) $params['id'], static::CANVAS_NAME.'canvas');
            if ($singleCanvas !== false) {
                $currentCanvasId = (int) $params['id'];
                $canvasTitle = $singleCanvas[0]['title'] ?? '';
                session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);
            }
        }

        if (isset($_POST['newCanvas'])) {
            $result = $this->handleNewCanvas($currentCanvasId);
            if ($result !== null) {
                return $result;
            }
        }

        if (isset($_POST['editCanvas']) && $currentCanvasId > 0) {
            $result = $this->handleEditCanvas($currentCanvasId);
            if ($result !== null) {
                return $result;
            }
        }

        $this->assignTemplateVars($currentCanvasId, $canvasTitle);

        if (! isset($_GET['raw'])) {
            return $this->tpl->displayPartial('canvas.boardDialog');
        }

        return new Response;
    }

    /**
     * Handles creating a new canvas board.
     */
    private function handleNewCanvas(int|string &$currentCanvasId): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        if ($this->canvasRepo->existCanvas(session('currentProject'), $_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');

            return null;
        }

        $values = [
            'title' => $_POST['canvastitle'],
            'author' => session('userdata.id'),
            'projectId' => session('currentProject'),
        ];
        // createBoard authorizes CREATE against the target (current) project.
        $currentCanvasId = $this->blueprintsService->createBoard($values, static::CANVAS_NAME.'canvas');

        $this->notifyBoardCreated($values['title']);

        $this->tpl->setNotification($this->language->__('notification.board_created'), 'success', static::CANVAS_NAME.'board_created');
        session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);

        return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/boardDialog/'.$currentCanvasId);
    }

    /**
     * Handles editing a canvas board title.
     */
    private function handleEditCanvas(int $currentCanvasId): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        if ($this->canvasRepo->existCanvas(session('currentProject'), $_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');

            return null;
        }

        // renameBoard authorizes EDIT against the board's real project.
        $this->blueprintsService->renameBoard($currentCanvasId, $_POST['canvastitle'], static::CANVAS_NAME.'canvas');

        $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/boardDialog/'.$currentCanvasId);
    }

    /**
     * Sends board creation notifications to project users.
     */
    private function notifyBoardCreated(string $title): void
    {
        $mailer = app()->make(MailerCore::class);
        $users = $this->projectService->getUsersToNotify(session('currentProject'));

        $mailer->setSubject($this->language->__('notification.board_created'));
        $message = sprintf(
            $this->language->__('email_notifications.canvas_created_message'),
            session('userdata.name'),
            "<a href='".CURRENT_URL."'>".strip_tags($title).'</a>'
        );
        $mailer->setHtml($message);

        $queue = app()->make(QueueRepository::class);
        $queue->queueMessageToUsers(
            $users,
            $message,
            $this->language->__('notification.board_created'),
            session('currentProject')
        );
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(int|string $currentCanvasId, string $canvasTitle): void
    {
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasName', static::CANVAS_NAME);
        $this->tpl->assign('canvasTitle', $canvasTitle);
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));
    }
}
