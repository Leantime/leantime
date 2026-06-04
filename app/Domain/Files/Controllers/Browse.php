<?php

namespace Leantime\Domain\Files\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Files\Permissions\FilesPermissions;
use Leantime\Domain\Files\Services\Files as FileService;
use Symfony\Component\HttpFoundation\Response;

class Browse extends Controller
{
    private FileService $filesService;

    /**
     * Initializes dependencies.
     */
    public function init(
        FileService $filesService
    ): void {
        $this->filesService = $filesService;
    }

    /**
     * Displays the file browser.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    #[RequiresPermission(FilesPermissions::VIEW)]
    public function get(array $params): Response
    {
        $this->assignTemplateVars();

        return $this->tpl->display('files.browse');
    }

    /**
     * Handles file uploads and deletions via POST.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    #[RequiresPermission(FilesPermissions::VIEW)]
    public function post(array $params): Response
    {
        $result = $this->filesService->handleFileAction($_POST, $_FILES, 'project', session('currentProject'));

        if ($result['action'] === 'delete') {
            if ($result['success'] === true) {
                $this->tpl->setNotification($this->language->__('notifications.file_deleted'), 'success', 'file_deleted');

                return Frontcontroller::redirect(BASE_URL.'/files/showAll'.(($_GET['modalPopUp'] ?? '') ? '?modalPopUp=true' : ''));
            }

            $this->tpl->setNotification($this->language->__('notifications.file_delete_error'), 'error');
        }

        if ($result['action'] === 'upload') {
            if ($result['success'] === true) {
                $this->tpl->setNotification('notifications.file_upload_success', 'success', 'file_created');
            } else {
                $this->tpl->setNotification('notifications.file_upload_error', 'error');
            }
        }

        $this->assignTemplateVars();

        return $this->tpl->display('files.browse');
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(): void
    {
        $this->tpl->assign('currentModule', session('currentProject'));
        $this->tpl->assign('modules', $this->filesService->getModules(session('userdata.id')));
        $this->tpl->assign('imgExtensions', $this->filesService->getImageExtensions());
        $this->tpl->assign('files', $this->filesService->getFilesByModule('project', session('currentProject')));
    }
}
