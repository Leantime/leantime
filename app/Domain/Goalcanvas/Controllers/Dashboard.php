<?php

namespace Leantime\Domain\Goalcanvas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Mailer;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepo;
use Symfony\Component\HttpFoundation\Response;

class Dashboard extends Controller
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'goal';

    private Projects $projectService;

    private Goalcanvas $goalService;

    private object $canvasRepo;

    /**
     * Initializes dependencies.
     */
    public function init(
        Projects $projectService,
        Goalcanvas $goalService
    ): void {
        $this->projectService = $projectService;
        $this->goalService = $goalService;
        $repoName = app()->getNamespace().'Domain\\goalcanvas\\Repositories\\goalcanvas';
        $this->canvasRepo = app()->make($repoName);
    }

    /**
     * Displays the goal canvas dashboard.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));
        $allCanvas = $this->ensureDefaultCanvas($allCanvas);

        $goalAnalytics = $this->calculateGoalAnalytics($allCanvas);
        $currentCanvasId = $this->resolveCurrentCanvasId($allCanvas, $params);

        if (isset($_REQUEST['searchCanvas'])) {
            $currentCanvasId = (int) $_REQUEST['searchCanvas'];
            session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $currentCanvasId]);

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas/');
        }

        $this->assignTemplateVars($currentCanvasId, $allCanvas, $goalAnalytics);

        if (! isset($_GET['raw'])) {
            return $this->tpl->display(static::CANVAS_NAME.'canvas.dashboard');
        }

        return new Response;
    }

    /**
     * Handles goal canvas mutations (create, edit, clone, merge, import).
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));
        $allCanvas = $this->ensureDefaultCanvas($allCanvas);

        $goalAnalytics = $this->calculateGoalAnalytics($allCanvas);
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
        $this->assignTemplateVars($currentCanvasId, $allCanvas, $goalAnalytics);

        if (! isset($_GET['raw'])) {
            return $this->tpl->display(static::CANVAS_NAME.'canvas.dashboard');
        }

        return new Response;
    }

    /**
     * Creates a default canvas if none exist.
     */
    private function ensureDefaultCanvas(array $allCanvas): array
    {
        if (! $allCanvas || count($allCanvas) == 0) {
            $values = [
                'title' => $this->language->__('label.board'),
                'author' => session('userdata.id'),
                'projectId' => session('currentProject'),
            ];
            $this->canvasRepo->addCanvas($values);

            return $this->canvasRepo->getAllCanvas(session('currentProject'));
        }

        return $allCanvas;
    }

    /**
     * Calculates goal analytics across all canvases.
     */
    private function calculateGoalAnalytics(array $allCanvas): array
    {
        $goalAnalytics = [
            'numCanvases' => count($allCanvas),
            'numGoals' => '0',
            'goalsOnTrack' => 0,
            'goalsAtRisk' => 0,
            'goalsMiss' => 0,
            'avgPercentComplete' => '0',
        ];

        $totalPercent = 0;
        foreach ($allCanvas as $canvas) {
            $canvasItems = $this->canvasRepo->getCanvasItemsById($canvas['id']);
            foreach ($canvasItems as $item) {
                $goalAnalytics['numGoals']++;

                if ($item['status'] == 'status_ontrack') {
                    $goalAnalytics['goalsOnTrack']++;
                }

                if ($item['status'] == 'status_atrisk') {
                    $goalAnalytics['goalsAtRisk']++;
                }

                if ($item['status'] == 'status_miss') {
                    $goalAnalytics['goalsMiss']++;
                }

                $total = $item['endValue'] - $item['startValue'];
                $progressValue = $item['currentValue'] - $item['startValue'];

                if ($total != 0) {
                    $percentDone = max(0, min(100, round($progressValue / $total * 100, 2)));
                } else {
                    $percentDone = 0;
                }

                $totalPercent = $totalPercent + $percentDone;
            }
        }

        if ($goalAnalytics['numGoals'] > 0) {
            $goalAnalytics['avgPercentComplete'] = $totalPercent / $goalAnalytics['numGoals'];
        }

        return $goalAnalytics;
    }

    /**
     * Resolves the current canvas ID from session or request parameters.
     */
    private function resolveCurrentCanvasId(array $allCanvas, array $params): int
    {
        $sessionKey = 'current'.strtoupper(static::CANVAS_NAME).'Canvas';

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
            $currentCanvasId = (int) $params['id'];
            session([$sessionKey => $currentCanvasId]);
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
        $currentCanvasId = $this->canvasRepo->addCanvas($values);
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

        $values = ['title' => $_POST['canvastitle'], 'id' => $currentCanvasId];
        $currentCanvasId = $this->canvasRepo->updateCanvas($values);

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

        $currentCanvasId = $this->canvasRepo->copyCanvas(
            session('currentProject'),
            $currentCanvasId,
            session('userdata.id'),
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

        $status = $this->canvasRepo->mergeCanvas($currentCanvasId, $_POST['canvasid']);

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

        $canvas = $this->canvasRepo->getSingleCanvas($currentCanvasId);
        $this->notifyBoardCreated(
            strip_tags($canvas[0]['title']),
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
    private function assignTemplateVars(int $currentCanvasId, array $allCanvas, array $goalAnalytics): void
    {
        $filter['status'] = $_GET['filter_status'] ?? (session('filter_status') ?? 'all');
        session(['filter_status' => $filter['status']]);
        $filter['relates'] = $_GET['filter_relates'] ?? (session('filter_relates') ?? 'all');
        session(['filter_relates' => $filter['relates']]);

        $this->tpl->assign('filter', $filter);
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('goalStats', $goalAnalytics);
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
