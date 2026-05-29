<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowCanvas controller - displays and manages a blueprint canvas board.
 *
 * Replaces the old per-variant Canvas\Controllers\ShowCanvas subclasses.
 * The canvas type slug comes from the route instead of a class constant.
 */
class ShowCanvas extends Controller
{
    private ProjectService $projectService;

    private BlueprintsRepository $blueprintsRepo;

    private BlueprintsService $blueprintsService;

    private TemplateRegistry $templateRegistry;

    private string $canvasSlug = '';

    private ?CanvasTemplate $template = null;

    /**
     * init - resolve dependencies and determine the canvas slug from request.
     *
     * @param  ProjectService  $projectService  Project service
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  BlueprintsService  $blueprintsService  Blueprints service
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function init(
        ProjectService $projectService,
        BlueprintsRepository $blueprintsRepo,
        BlueprintsService $blueprintsService,
        TemplateRegistry $templateRegistry
    ): void {
        $this->projectService = $projectService;
        $this->blueprintsRepo = $blueprintsRepo;
        $this->blueprintsService = $blueprintsService;
        $this->templateRegistry = $templateRegistry;

        $this->canvasSlug = strip_tags(request()->route('canvasSlug') ?? ($_GET['canvasSlug'] ?? ''));
        $this->template = $this->templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - display the canvas board (and handle the board switcher).
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function get(array $params): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $canvasType = $this->template->getDatabaseType();
        $sessionKey = $this->template->getSessionKey();

        [$allCanvas, $currentCanvasId] = $this->resolveCurrentBoard($params, $canvasType, $sessionKey);

        // Board switcher
        if (isset($params['searchCanvas'])) {
            session([$sessionKey => (int) $params['searchCanvas']]);

            return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
        }

        return $this->renderCanvas($params, $allCanvas, $currentCanvasId);
    }

    /**
     * post - handle create / edit / clone / merge / import board actions.
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

        [$allCanvas, $currentCanvasId] = $this->resolveCurrentBoard($params, $canvasType, $sessionKey);

        // Add board
        if (isset($params['newCanvas'])) {
            if (isset($params['canvastitle']) && ! empty($params['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $params['canvastitle'], $canvasType)) {
                    $values = [
                        'title' => $params['canvastitle'],
                        'author' => session('userdata.id'),
                        'projectId' => session('currentProject'),
                    ];
                    $currentCanvasId = $this->blueprintsRepo->addCanvas($values, $canvasType);

                    $this->notifyBoardChange(
                        'email_notifications.canvas_created_message',
                        'notification.board_created',
                        $values['title']
                    );

                    $this->tpl->setNotification(
                        $this->language->__('notification.board_created'),
                        'success',
                        $this->canvasSlug.'board_created'
                    );

                    session([$sessionKey => $currentCanvasId]);

                    return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
                }

                $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        // Edit board
        if (isset($params['editCanvas']) && is_int($currentCanvasId) && $currentCanvasId > 0) {
            if (isset($params['canvastitle']) && ! empty($params['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $params['canvastitle'], $canvasType)) {
                    $this->blueprintsRepo->updateCanvas(['title' => $params['canvastitle'], 'id' => $currentCanvasId]);

                    $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');

                    return $this->tpl->displayPartial('blueprints.boardDialog');
                }

                $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        // Clone board
        if (isset($params['cloneCanvas']) && is_int($currentCanvasId) && $currentCanvasId > 0) {
            if (isset($params['canvastitle']) && ! empty($params['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $params['canvastitle'], $canvasType)) {
                    $currentCanvasId = $this->blueprintsRepo->copyCanvas(
                        session('currentProject'),
                        $currentCanvasId,
                        session('userdata.id'),
                        $params['canvastitle'],
                        $canvasType
                    );

                    $this->tpl->setNotification($this->language->__('notification.board_copied'), 'success');

                    session([$sessionKey => $currentCanvasId]);

                    return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
                }

                $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        // Merge board
        if (isset($params['mergeCanvas']) && is_int($currentCanvasId) && $currentCanvasId > 0) {
            if (isset($params['canvasid']) && $params['canvasid'] > 0) {
                if ($this->blueprintsRepo->mergeCanvas($currentCanvasId, $params['canvasid'])) {
                    $this->tpl->setNotification($this->language->__('notification.board_merged'), 'success');

                    return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
                }

                $this->tpl->setNotification($this->language->__('notification.merge_error'), 'error');
            } else {
                $this->tpl->setNotification($this->language->__('notification.internal_error'), 'error');
            }
        }

        // Import board
        if (isset($params['importCanvas']) && isset($_FILES['canvasfile']) && $_FILES['canvasfile']['error'] === 0) {
            $uploadfile = tempnam(sys_get_temp_dir(), 'leantime.').'.xml';

            if (move_uploaded_file($_FILES['canvasfile']['tmp_name'], $uploadfile)) {
                $importCanvasId = $this->blueprintsService->import(
                    $uploadfile,
                    $this->canvasSlug,
                    projectId: session('currentProject'),
                    authorId: session('userdata.id')
                );
                unlink($uploadfile);

                if ($importCanvasId !== false) {
                    session([$sessionKey => $importCanvasId]);
                    $canvas = $this->blueprintsRepo->getSingleCanvas((int) $importCanvasId, $canvasType);

                    $this->notifyBoardChange(
                        'email_notifications.canvas_imported_message',
                        'notification.board_imported',
                        $canvas[0]['title'] ?? ''
                    );

                    $this->tpl->setNotification($this->language->__('notification.board_imported'), 'success');

                    return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
                }
            }

            $this->tpl->setNotification($this->language->__('notification.board_import_failed'), 'error');
        }

        return $this->renderCanvas($params, $allCanvas, $currentCanvasId);
    }

    /**
     * resolveCurrentBoard - load the project's boards and determine the active board id.
     *
     * Creates a default board if none exist, validates the session board against the
     * available boards, and honours an explicit id from the request.
     *
     * @param  array<string, mixed>  $params  Request parameters
     * @param  string  $canvasType  Database canvas type
     * @param  string  $sessionKey  Session key for the active board
     * @return array{0: array<int, array<string, mixed>>, 1: int} [allCanvas, currentCanvasId]
     */
    private function resolveCurrentBoard(array $params, string $canvasType, string $sessionKey): array
    {
        $allCanvas = $this->blueprintsRepo->getAllCanvas(session('currentProject'), $canvasType);

        // Create a default board when the project has none.
        if (! $allCanvas || count($allCanvas) == 0) {
            $this->blueprintsRepo->addCanvas([
                'title' => $this->language->__('label.board'),
                'author' => session('userdata.id'),
                'projectId' => session('currentProject'),
            ], $canvasType);
            $allCanvas = $this->blueprintsRepo->getAllCanvas(session('currentProject'), $canvasType);
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
            $currentCanvasId = (int) $params['id'];
            session([$sessionKey => $currentCanvasId]);
        }

        return [$allCanvas, $currentCanvasId];
    }

    /**
     * renderCanvas - assign template data and render the canvas board page.
     *
     * @param  array<string, mixed>  $params  Request parameters
     * @param  array<int, array<string, mixed>>  $allCanvas  All boards for the project
     * @param  int  $currentCanvasId  Active board id
     */
    private function renderCanvas(array $params, array $allCanvas, int $currentCanvasId): Response
    {
        $filter['status'] = $params['filter_status'] ?? (session('filter_status') ?? 'all');
        session(['filter_status' => $filter['status']]);
        $filter['relates'] = $params['filter_relates'] ?? (session('filter_relates') ?? 'all');
        session(['filter_relates' => $filter['relates']]);

        $this->tpl->assign('filter', $filter);
        $this->tpl->assign('currentCanvas', $currentCanvasId);
        $this->tpl->assign('canvasSlug', $this->canvasSlug);
        $this->tpl->assign('template', $this->template);
        $this->tpl->assign('canvasIcon', $this->template->icon);
        $this->tpl->assign('canvasTypes', $this->blueprintsService->getTranslatedBoxes($this->template));
        $this->tpl->assign('statusLabels', $this->blueprintsService->getTranslatedStatusLabels($this->template));
        $this->tpl->assign('relatesLabels', $this->blueprintsService->getTranslatedRelatesLabels($this->template));
        $this->tpl->assign('dataLabels', $this->blueprintsService->getTranslatedDataLabels($this->template));
        $this->tpl->assign('disclaimer', $this->blueprintsService->getTranslatedDisclaimer($this->template));
        $this->tpl->assign('allCanvas', $allCanvas);
        $this->tpl->assign('canvasItems', $this->blueprintsRepo->getCanvasItemsById($currentCanvasId, $this->template->getCommentModule()));
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));

        return $this->tpl->display('blueprints.showCanvas');
    }

    /**
     * notifyBoardChange - email + queue notify project users about a board change.
     *
     * @param  string  $messageKey  i18n key for the email body (sprintf: user name, board link/title)
     * @param  string  $subjectKey  i18n key for the subject/queue title
     * @param  string  $boardTitle  Board title
     */
    private function notifyBoardChange(string $messageKey, string $subjectKey, string $boardTitle): void
    {
        $mailer = app()->make(MailerCore::class);
        $users = $this->projectService->getUsersToNotify(session('currentProject'));

        $mailer->setSubject($this->language->__($subjectKey));

        $message = sprintf(
            $this->language->__($messageKey),
            session('userdata.name'),
            "<a href='".CURRENT_URL."'>".strip_tags($boardTitle).'</a>'
        );
        $mailer->setHtml($message);

        $queue = app()->make(QueueRepository::class);
        $queue->queueMessageToUsers(
            $users,
            $message,
            $this->language->__($subjectKey),
            session('currentProject')
        );
    }
}
