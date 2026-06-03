<?php

namespace Leantime\Domain\Goalcanvas\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Mailer;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Goalcanvas\Permissions\GoalcanvasPermissions;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepo;
use Symfony\Component\HttpFoundation\Response;

class ShowCanvas extends Controller
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'goal';

    private $canvasRepo;

    private Projects $projectService;

    private Goalcanvas $goalService;

    /**
     * Initializes dependencies.
     */
    public function init(Projects $projectService, Goalcanvas $goalService): void
    {
        $this->projectService = $projectService;
        $this->goalService = $goalService;
        $repoName = app()->getNamespace().'Domain\\goalcanvas\\Repositories\\goalcanvas';
        $this->canvasRepo = app()->make($repoName);
    }

    /**
     * Displays the goal canvas board view.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(GoalcanvasPermissions::VIEW)]
    public function get(array $params): Response
    {
        $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));
        $currentCanvasId = $this->resolveCurrentCanvasId($allCanvas, $params);

        if (isset($_REQUEST['searchCanvas'])) {
            $currentCanvasId = (int) $_REQUEST['searchCanvas'];
            session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas/');
        }

        $this->assignTemplateVars($currentCanvasId, $allCanvas);

        if (! isset($_GET['raw'])) {
            return $this->tpl->display(static::CANVAS_NAME.'canvas.showCanvas');
        }

        return new Response;
    }

    /**
     * Handles goal canvas mutations (create, edit, clone, merge, import).
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(GoalcanvasPermissions::EDIT)]
    public function post(array $params): Response
    {
        $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));
        $currentCanvasId = $this->resolveCurrentCanvasId($allCanvas, $params);

        if (isset($_POST['newCanvas'])) {
            $result = $this->handleNewCanvas($currentCanvasId, $allCanvas);
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

        if (isset($_POST['cloneCanvas']) && $currentCanvasId > 0) {
            $result = $this->handleCloneCanvas($currentCanvasId, $allCanvas);
            if ($result !== null) {
                return $result;
            }
        }

        if (isset($_POST['mergeCanvas']) && $currentCanvasId > 0) {
            $result = $this->handleMergeCanvas($currentCanvasId);
            if ($result !== null) {
                return $result;
            }
        }

        if (isset($_POST['importCanvas'])) {
            $result = $this->handleImportCanvas($currentCanvasId, $allCanvas);
            if ($result !== null) {
                return $result;
            }
        }

        $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));
        $this->assignTemplateVars($currentCanvasId, $allCanvas);

        if (! isset($_GET['raw'])) {
            return $this->tpl->display(static::CANVAS_NAME.'canvas.showCanvas');
        }

        return new Response;
    }

    /**
     * Resolves the current canvas ID from session, GET params, or creates a default.
     */
    private function resolveCurrentCanvasId(array &$allCanvas, array $params): int
    {
        $sessionKey = 'current'.strtoupper(static::CANVAS_NAME).'Canvas';

        if (! $allCanvas || count($allCanvas) == 0) {
            $values = [
                'title' => $this->language->__('label.board'),
                'author' => session('userdata.id'),
                'projectId' => session('currentProject'),
            ];
            // View-time convenience: lazily create a default board (in the CURRENT project) when
            // none exist. Kept repo-direct/UNGATED on purpose — gating it through createGoalboard's
            // CREATE check would 403 a VIEW-only user just for opening an empty goals page. It is
            // not an IDOR (always the session project). Same landmine pattern as the Blueprints/
            // Wiki/Ideas default-board/notebook bootstrap.
            $currentCanvasId = $this->canvasRepo->addCanvas($values);
            $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));

            return $currentCanvasId;
        }

        $currentCanvasId = -1;

        if (session()->exists($sessionKey)) {
            $currentCanvasId = session($sessionKey);

            $found = false;
            foreach ($allCanvas as $row) {
                if ($currentCanvasId == $row['id']) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $currentCanvasId = -1;
                session([$sessionKey => '']);
            }
        } else {
            session([$sessionKey => '']);
        }

        if (count($allCanvas) > 0 && session($sessionKey) == '') {
            $currentCanvasId = $allCanvas[0]['id'];
            session([$sessionKey => $currentCanvasId]);
        }

        if (isset($params['id'])) {
            // Only honor an explicit board id that belongs to the CURRENT project's boards
            // ($allCanvas is project-scoped). A foreign/unknown id must not become the active
            // board — otherwise assignTemplateVars would read another project's items.
            $requestedId = (int) $params['id'];
            $projectBoardIds = array_map(static fn ($row) => (int) $row['id'], $allCanvas);
            if (in_array($requestedId, $projectBoardIds, true)) {
                $currentCanvasId = $requestedId;
                session([$sessionKey => $currentCanvasId]);
            }
        }

        return $currentCanvasId;
    }

    /**
     * Handles creating a new canvas board.
     */
    private function handleNewCanvas(int &$currentCanvasId, array &$allCanvas): ?Response
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
        // createGoalboard authorizes CREATE against the target (current) project.
        $currentCanvasId = $this->goalService->createGoalboard($values);
        $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));

        $this->notifyBoardCreated($values['title'], 'notification.board_created', 'email_notifications.canvas_created_message');

        $this->tpl->setNotification($this->language->__('notification.board_created'), 'success');
        session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);

        return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas/');
    }

    /**
     * Handles editing a canvas board title.
     */
    private function handleEditCanvas(int &$currentCanvasId): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        if ($this->canvasRepo->existCanvas(session('currentProject'), $_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');

            return null;
        }

        // updateGoalboard authorizes EDIT against the board's real project.
        $values = ['title' => $_POST['canvastitle'], 'id' => $currentCanvasId];
        $currentCanvasId = $this->goalService->updateGoalboard($values);

        $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas/');
    }

    /**
     * Handles cloning a canvas board.
     */
    private function handleCloneCanvas(int &$currentCanvasId, array &$allCanvas): ?Response
    {
        if (! isset($_POST['canvastitle']) || empty($_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

            return null;
        }

        if ($this->canvasRepo->existCanvas(session('currentProject'), $_POST['canvastitle'])) {
            $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');

            return null;
        }

        // copyGoalBoard authorizes VIEW on the source board's project and CREATE on the target.
        $currentCanvasId = $this->goalService->copyGoalBoard(
            $currentCanvasId,
            (int) session('currentProject'),
            (int) session('userdata.id'),
            $_POST['canvastitle']
        );
        $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));

        $this->tpl->setNotification($this->language->__('notification.board_copied'), 'success');
        session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);

        return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas/');
    }

    /**
     * Handles merging two canvas boards.
     */
    private function handleMergeCanvas(int $currentCanvasId): ?Response
    {
        if (! isset($_POST['canvasid']) || $_POST['canvasid'] <= 0) {
            $this->tpl->setNotification($this->language->__('notification.internal_error'), 'error');

            return null;
        }

        // mergeGoalBoard authorizes EDIT on the target board's project and VIEW on the source's.
        $status = $this->goalService->mergeGoalBoard($currentCanvasId, (int) $_POST['canvasid']);

        if ($status) {
            $this->tpl->setNotification($this->language->__('notification.board_merged'), 'success');

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas/');
        }

        $this->tpl->setNotification($this->language->__('notification.merge_error'), 'error');

        return null;
    }

    /**
     * Handles importing a canvas from an XML file.
     */
    private function handleImportCanvas(int &$currentCanvasId, array &$allCanvas): ?Response
    {
        if (! isset($_FILES['canvasfile']) || $_FILES['canvasfile']['error'] !== 0) {
            return null;
        }

        $uploadfile = tempnam(sys_get_temp_dir(), 'leantime.').'.xml';

        if (! move_uploaded_file($_FILES['canvasfile']['tmp_name'], $uploadfile)) {
            $this->tpl->setNotification($this->language->__('notification.board_import_failed'), 'error');

            return null;
        }

        $services = app()->make(BlueprintsService::class);
        // Blueprints service expects the canvas slug (e.g. "goal"), not the full type ("goalcanvas").
        $importCanvasId = $services->import(
            $uploadfile,
            static::CANVAS_NAME,
            projectId: session('currentProject'),
            authorId: session('userdata.id')
        );
        unlink($uploadfile);

        if ($importCanvasId === false) {
            $this->tpl->setNotification($this->language->__('notification.board_import_failed'), 'error');

            return null;
        }

        $currentCanvasId = $importCanvasId;
        $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));
        session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);

        // The just-imported board is in the current project; getSingleCanvas returns a single
        // row (Goalcanvas repo override) or false.
        $canvas = $this->goalService->getSingleCanvas($currentCanvasId);
        $this->notifyBoardCreated(
            strip_tags($canvas !== false ? ($canvas['title'] ?? '') : ''),
            'notification.board_imported',
            'email_notifications.canvas_imported_message'
        );

        $this->tpl->setNotification($this->language->__('notification.board_imported'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas/');
    }

    /**
     * Sends board creation/import notifications to project users.
     */
    private function notifyBoardCreated(string $title, string $subjectKey, string $messageKey): void
    {
        $mailer = app()->make(Mailer::class);
        $users = $this->projectService->getUsersToNotify(session('currentProject'));

        $mailer->setSubject($this->language->__($subjectKey));
        $message = sprintf(
            $this->language->__($messageKey),
            session('userdata.name'),
            "<a href='".CURRENT_URL."'>".strip_tags($title).'</a>'
        );
        $mailer->setHtml($message);

        $queue = app()->make(QueueRepo::class);
        $queue->queueMessageToUsers(
            $users,
            $message,
            $this->language->__($subjectKey),
            session('currentProject')
        );
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(int $currentCanvasId, array $allCanvas): void
    {
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasIcon', $this->canvasRepo->getIcon());
        $this->tpl->assign('canvasTypes', $this->canvasRepo->getCanvasTypes());
        $this->tpl->assign('statusLabels', $this->canvasRepo->getStatusLabels());
        $this->tpl->assign('relatesLabels', $this->canvasRepo->getRelatesLabels());
        $this->tpl->assign('dataLabels', $this->canvasRepo->getDataLabels());
        $this->tpl->assign('disclaimer', $this->canvasRepo->getDisclaimer());
        $this->tpl->assign('allCanvas', $allCanvas);
        $this->tpl->assign('canvasItems', $this->goalService->getCanvasItemsById($currentCanvasId));
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));
    }
}
