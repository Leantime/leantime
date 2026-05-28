<?php

namespace Leantime\Domain\Files\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Files\Services\Files as FileService;
use Symfony\Component\HttpFoundation\Response;

class ShowAll extends Controller
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
     * Displays all project files.
     *
     * @param  array  $params  Request parameters
     *
     * @throws BindingResolutionException
     */
    public function get(array $params): Response
    {
        $currentModule = $params['id'] ?? $_GET['id'] ?? '';

        if (isset($_GET['delFile'])) {
            $result = $this->filesService->deleteFile($_GET['delFile']);

            if ($result === true) {
                $this->tpl->setNotification($this->language->__('notifications.file_deleted'), 'success', 'file_deleted');

                return Frontcontroller::redirect(BASE_URL.'/files/showAll'.(($_GET['modalPopUp'] ?? '') ? '?modalPopUp=true' : ''));
            } else {
                $this->tpl->setNotification($result['msg'], 'success');
            }
        }

        $this->assignTemplateVars($currentModule);

        return $this->tpl->displayPartial('files.showAll');
    }

    /**
     * Handles file uploads.
     *
     * @param  array  $params  Request parameters
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        $currentModule = $params['id'] ?? $_GET['id'] ?? '';

        if (isset($_POST['upload']) || isset($_FILES['file'])) {
            if (isset($_FILES['file'])) {
                $this->filesService->upload($_FILES, 'project', session('currentProject'));
                $this->tpl->setNotification('notifications.file_upload_success', 'success', 'file_uploaded');
            } else {
                $this->tpl->setNotification('notifications.file_upload_error', 'error');
            }
        }

        $this->assignTemplateVars($currentModule);

        return $this->tpl->displayPartial('files.showAll');
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(string $currentModule): void
    {
        $this->tpl->assign('currentModule', $currentModule);
        $this->tpl->assign('modules', $this->filesService->getModules(session('userdata.id')));
        $this->tpl->assign('imgExtensions', ['jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv']);
        $this->tpl->assign('files', $this->filesService->getFilesByModule('project', session('currentProject'), session('userdata.id')));
    }
}
