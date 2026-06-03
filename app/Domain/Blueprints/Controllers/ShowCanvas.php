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
 * ShowCanvas controller - displays and manages a blueprint canvas board.
 *
 * Replaces the old per-variant Canvas\Controllers\ShowCanvas subclasses.
 * The canvas type slug comes from the route instead of a class constant.
 *
 * Native Laravel controller: route-bound actions, the {canvasSlug}/{id} path segments
 * arrive via the route (canvasSlug resolved in the constructor, id as a typed action arg),
 * and request input is read from the injected IncomingRequest instead of the legacy
 * merged-$params argument and superglobals.
 */
class ShowCanvas
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
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  BlueprintsService  $blueprintsService  Blueprints service
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function __construct(
        private IncomingRequest $request,
        private Template $tpl,
        private Language $language,
        private ProjectService $projectService,
        private BlueprintsRepository $blueprintsRepo,
        private BlueprintsService $blueprintsService,
        TemplateRegistry $templateRegistry,
    ) {
        $this->canvasSlug = strip_tags((string) ($request->route('canvasSlug') ?? ''));
        $this->template = $templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - display the canvas board (and handle the board switcher).
     *
     * @param  string|null  $id  Active board id from the route
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
        $sessionKey = $this->template->getSessionKey();

        [$allCanvas, $currentCanvasId] = $this->resolveCurrentBoard($data, $canvasType, $sessionKey);

        // Board switcher
        if (isset($data['searchCanvas'])) {
            session([$sessionKey => (int) $data['searchCanvas']]);

            return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
        }

        return $this->renderCanvas($data, $allCanvas, $currentCanvasId);
    }

    /**
     * post - handle create / edit / clone / merge / import board actions.
     *
     * @param  string|null  $id  Active board id from the route
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
        $sessionKey = $this->template->getSessionKey();

        [$allCanvas, $currentCanvasId] = $this->resolveCurrentBoard($data, $canvasType, $sessionKey);

        // Add board
        if (isset($data['newCanvas'])) {
            if (isset($data['canvastitle']) && ! empty($data['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $data['canvastitle'], $canvasType)) {
                    $values = [
                        'title' => $data['canvastitle'],
                        'author' => session('userdata.id'),
                        'projectId' => session('currentProject'),
                    ];
                    // createBoard authorizes CREATE against the target (current) project.
                    $currentCanvasId = $this->blueprintsService->createBoard($values, $canvasType);

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
        if (isset($data['editCanvas']) && is_int($currentCanvasId) && $currentCanvasId > 0) {
            if (isset($data['canvastitle']) && ! empty($data['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $data['canvastitle'], $canvasType)) {
                    // renameBoard authorizes EDIT against the board's real project.
                    $this->blueprintsService->renameBoard($currentCanvasId, $data['canvastitle'], $canvasType);

                    $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');

                    return $this->tpl->displayPartial('blueprints.boardDialog');
                }

                $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        // Clone board
        if (isset($data['cloneCanvas']) && is_int($currentCanvasId) && $currentCanvasId > 0) {
            if (isset($data['canvastitle']) && ! empty($data['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $data['canvastitle'], $canvasType)) {
                    // copyBoard authorizes VIEW on the source board's real project and CREATE
                    // on the target (current) project.
                    $currentCanvasId = $this->blueprintsService->copyBoard(
                        $currentCanvasId,
                        (int) session('currentProject'),
                        (int) session('userdata.id'),
                        $data['canvastitle'],
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
        if (isset($data['mergeCanvas']) && is_int($currentCanvasId) && $currentCanvasId > 0) {
            if (isset($data['canvasid']) && $data['canvasid'] > 0) {
                // mergeBoard authorizes EDIT on the target board's project and VIEW on the
                // source board's project — both resolved by id, so neither can cross projects.
                if ($this->blueprintsService->mergeBoard($currentCanvasId, (int) $data['canvasid'], $canvasType)) {
                    $this->tpl->setNotification($this->language->__('notification.board_merged'), 'success');

                    return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
                }

                $this->tpl->setNotification($this->language->__('notification.merge_error'), 'error');
            } else {
                $this->tpl->setNotification($this->language->__('notification.internal_error'), 'error');
            }
        }

        // Import board
        if (isset($data['importCanvas']) && isset($_FILES['canvasfile']) && $_FILES['canvasfile']['error'] === 0) {
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
                    $canvas = $this->blueprintsService->getBoard((int) $importCanvasId, $canvasType);

                    $this->notifyBoardChange(
                        'email_notifications.canvas_imported_message',
                        'notification.board_imported',
                        $canvas !== false ? ($canvas[0]['title'] ?? '') : ''
                    );

                    $this->tpl->setNotification($this->language->__('notification.board_imported'), 'success');

                    return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
                }
            }

            $this->tpl->setNotification($this->language->__('notification.board_import_failed'), 'error');
        }

        return $this->renderCanvas($data, $allCanvas, $currentCanvasId);
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
            // Cast: DB drivers (MySQL emulated prepares) return ids as strings.
            $currentCanvasId = (int) session($sessionKey);

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
            $currentCanvasId = (int) $allCanvas[0]['id'];
            session([$sessionKey => $currentCanvasId]);
        }

        if (isset($params['id'])) {
            // Only honor an explicit board id that belongs to the CURRENT project's boards
            // ($allCanvas is project-scoped). A foreign/unknown id must not become the active
            // board — otherwise renderCanvas would read another project's items (IDOR).
            $requestedId = (int) $params['id'];
            $projectBoardIds = array_map(static fn ($row) => (int) $row['id'], $allCanvas);
            if (in_array($requestedId, $projectBoardIds, true)) {
                $currentCanvasId = $requestedId;
                session([$sessionKey => $currentCanvasId]);
            }
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
        // getBoardItems authorizes VIEW against the board's real project and returns [] for a
        // foreign/unknown board, so a board id from another project can't leak its items here.
        $this->tpl->assign('canvasItems', $this->blueprintsService->getBoardItems($currentCanvasId, $this->template->getDatabaseType(), $this->template->getCommentModule()));
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
