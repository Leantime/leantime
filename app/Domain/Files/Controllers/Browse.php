<?php

namespace Leantime\Domain\Files\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
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
    public function post(array $params): Response
    {
        if (isset($_POST['delFile'])) {
            $result = $this->filesService->deleteFile($_POST['delFile']);

            if ($result === true) {
                $this->tpl->setNotification($this->language->__('notifications.file_deleted'), 'success', 'file_deleted');

                return Frontcontroller::redirect(BASE_URL.'/files/showAll'.(($_GET['modalPopUp'] ?? '') ? '?modalPopUp=true' : ''));
            } else {
                $this->tpl->setNotification($this->language->__('notifications.file_delete_error'), 'error');
            }
        }

        if (isset($_POST['upload']) || isset($_FILES['file'])) {
            if (isset($_FILES['file'])) {
                $this->filesService->upload($_FILES, 'project', session('currentProject'));
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
        $this->tpl->assign('imgExtensions', ['jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv', 'webp']);
        $this->tpl->assign('files', $this->filesService->getFilesByModule('project', session('currentProject')));
    }
}
