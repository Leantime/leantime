<?php

namespace Leantime\Domain\Ideas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Ideas\Repositories\Ideas;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Symfony\Component\HttpFoundation\Response;

class BoardDialog extends Controller
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = '??';

    private ProjectService $projectService;

    private object $canvasRepo;

    /**
     * Initializes dependencies.
     */
    public function init(ProjectService $projectService): void
    {
        $this->projectService = $projectService;
        $this->canvasRepo = app()->make(Ideas::class);
    }

    /**
     * Displays the board dialog form.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        $currentCanvasId = '';
        $canvasTitle = '';

        if (isset($params['id'])) {
            $currentCanvasId = (int) $params['id'];
            $singleCanvas = $this->canvasRepo->getSingleCanvas($currentCanvasId);
            $canvasTitle = $singleCanvas[0]['title'] ?? '';
            session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);
        }

        $this->tpl->assign('canvasTitle', $canvasTitle);
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasname', 'idea');
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));

        if (! isset($_GET['raw'])) {
            return $this->tpl->displayPartial('ideas.boardDialog');
        }

        return new Response;
    }

    /**
     * Handles board creation and editing.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        $currentCanvasId = '';
        $canvasTitle = '';

        if (isset($params['id'])) {
            $currentCanvasId = (int) $params['id'];
            $singleCanvas = $this->canvasRepo->getSingleCanvas($currentCanvasId);
            $canvasTitle = $singleCanvas[0]['title'] ?? '';
            session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);
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

        $this->tpl->assign('canvasTitle', $canvasTitle);
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasname', 'idea');
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));

        if (! isset($_GET['raw'])) {
            return $this->tpl->displayPartial('ideas.boardDialog');
        }

        return new Response;
    }

    /**
     * Handles creating a new board.
     */
    private function handleNewCanvas(int|string &$currentCanvasId): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        $values = [
            'title' => $_POST['canvastitle'],
            'author' => session('userdata.id'),
            'projectId' => session('currentProject'),
        ];
        $currentCanvasId = $this->canvasRepo->addCanvas($values);

        $this->notifyBoardCreated($values['title']);

        $this->tpl->setNotification($this->language->__('notification.board_created'), 'success', static::CANVAS_NAME.'board_created');
        session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);

        return Frontcontroller::redirect(BASE_URL.'/ideas/boardDialog/'.$currentCanvasId);
    }

    /**
     * Handles editing a board title.
     */
    private function handleEditCanvas(int $currentCanvasId): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        $values = ['title' => $_POST['canvastitle'], 'id' => $currentCanvasId];
        $this->canvasRepo->updateCanvas($values);

        $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/ideas/boardDialog/'.$values['id']);
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
}
