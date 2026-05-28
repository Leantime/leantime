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
 * The canvas type slug comes from a GET parameter instead of a class constant.
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
     * run - display the canvas board and handle create/edit/clone/merge/import actions.
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

        $allCanvas = $this->blueprintsRepo->getAllCanvas(session('currentProject'), $canvasType);

        // Create default canvas
        if (! $allCanvas || count($allCanvas) == 0) {
            $values = [
                'title' => $this->language->__('label.board'),
                'author' => session('userdata.id'),
                'projectId' => session('currentProject'),
            ];
            $currentCanvasId = $this->blueprintsRepo->addCanvas($values, $canvasType);
            $allCanvas = $this->blueprintsRepo->getAllCanvas(session('currentProject'), $canvasType);
        }

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
            $currentCanvasId = -1;
            session([$sessionKey => '']);
        }

        if (count($allCanvas) > 0 && session($sessionKey) == '') {
            $currentCanvasId = $allCanvas[0]['id'];
            session([$sessionKey => $currentCanvasId]);
        }

        if (isset($_GET['id']) === true) {
            $currentCanvasId = (int) $_GET['id'];
            session([$sessionKey => $currentCanvasId]);
        }

        if (isset($_REQUEST['searchCanvas']) === true) {
            $currentCanvasId = (int) $_REQUEST['searchCanvas'];
            session([$sessionKey => $currentCanvasId]);

            return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
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

                    return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
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

                    return $this->tpl->displayPartial('blueprints.boardDialog');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        // Clone canvas
        if (isset($_POST['cloneCanvas']) && $currentCanvasId > 0) {
            if (isset($_POST['canvastitle']) && ! empty($_POST['canvastitle'])) {
                if (! $this->blueprintsRepo->existCanvas(session('currentProject'), $_POST['canvastitle'], $canvasType)) {
                    $currentCanvasId = $this->blueprintsRepo->copyCanvas(
                        session('currentProject'),
                        $currentCanvasId,
                        session('userdata.id'),
                        $_POST['canvastitle'],
                        $canvasType
                    );
                    $allCanvas = $this->blueprintsRepo->getAllCanvas(session('currentProject'), $canvasType);

                    $this->tpl->setNotification($this->language->__('notification.board_copied'), 'success');

                    session([$sessionKey => $currentCanvasId]);

                    return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.board_exists'), 'error');
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
            }
        }

        // Merge canvas
        if (isset($_POST['mergeCanvas']) && $currentCanvasId > 0) {
            if (isset($_POST['canvasid']) && $_POST['canvasid'] > 0) {
                $status = $this->blueprintsRepo->mergeCanvas($currentCanvasId, $_POST['canvasid']);

                if ($status) {
                    $this->tpl->setNotification($this->language->__('notification.board_merged'), 'success');

                    return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.merge_error'), 'error');
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.internal_error'), 'error');
            }
        }

        // Import canvas
        if (isset($_POST['importCanvas'])) {
            if (isset($_FILES['canvasfile']) && $_FILES['canvasfile']['error'] === 0) {
                $uploadfile = tempnam(sys_get_temp_dir(), 'leantime.').'.xml';

                $status = move_uploaded_file($_FILES['canvasfile']['tmp_name'], $uploadfile);
                if ($status) {
                    $importCanvasId = $this->blueprintsService->import(
                        $uploadfile,
                        $this->canvasSlug,
                        projectId: session('currentProject'),
                        authorId: session('userdata.id')
                    );
                    unlink($uploadfile);

                    if ($importCanvasId !== false) {
                        $currentCanvasId = $importCanvasId;
                        $allCanvas = $this->blueprintsRepo->getAllCanvas(session('currentProject'), $canvasType);
                        session([$sessionKey => $currentCanvasId]);

                        $mailer = app()->make(MailerCore::class);
                        $this->projectService = app()->make(ProjectService::class);
                        $users = $this->projectService->getUsersToNotify(session('currentProject'));
                        $canvas = $this->blueprintsRepo->getSingleCanvas($currentCanvasId, $canvasType);
                        $mailer->setSubject($this->language->__('notification.board_imported'));

                        $actualLink = CURRENT_URL;
                        $message = sprintf(
                            $this->language->__('email_notifications.canvas_imported_message'),
                            session('userdata.name'),
                            "<a href='".$actualLink."'>".strip_tags($canvas[0]['title']).'</a>'
                        );
                        $mailer->setHtml($message);

                        $queue = app()->make(QueueRepository::class);
                        $queue->queueMessageToUsers(
                            $users,
                            $message,
                            $this->language->__('notification.board_imported'),
                            session('currentProject')
                        );

                        $this->tpl->setNotification($this->language->__('notification.board_imported'), 'success');

                        return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas/');
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.board_import_failed'), 'error');
                    }
                } else {
                    $this->tpl->setNotification($this->language->__('notification.board_import_failed'), 'error');
                }
            }
        }

        $filter['status'] = $_GET['filter_status'] ?? (session('filter_status') ?? 'all');
        session(['filter_status' => $filter['status']]);
        $filter['relates'] = $_GET['filter_relates'] ?? (session('filter_relates') ?? 'all');
        session(['filter_relates' => $filter['relates']]);

        $commentModule = $this->template->getCommentModule();

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
        $this->tpl->assign('canvasItems', $this->blueprintsRepo->getCanvasItemsById($currentCanvasId, $commentModule));
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));

        if (! isset($_GET['raw'])) {
            return $this->tpl->display('blueprints.showCanvas');
        }
    }
}
