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

        $this->assignTemplateVars($currentModule);

        return $this->tpl->displayPartial('files.showAll');
    }

    /**
     * Handles file uploads and deletions via POST.
     *
     * @param  array  $params  Request parameters
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        $currentModule = $params['id'] ?? $_GET['id'] ?? '';

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
        $this->tpl->assign('imgExtensions', $this->filesService->getImageExtensions());
        $this->tpl->assign('files', $this->filesService->getFilesByModule('project', session('currentProject'), session('userdata.id')));
    }
}
