<?php

namespace Leantime\Domain\Files\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Files\Services\Files as FileService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class ShowAll extends Controller
{
    private FileRepository $filesRepo;
    private FileService $filesService;

    /**
     * @param FileRepository $filesRepo
     * @param FileService    $filesService
     * @return void
     */
    public function init(
        FileRepository $filesRepo,
        FileService $filesService
    ): void {
        $this->filesRepo = $filesRepo;
        $this->filesService = $filesService;
    }

    /**
     * @return Response
     * @throws BindingResolutionException
     */
    public function run(): Response
    {

        $currentModule = '';
        if (isset($_GET['id'])) {
            $currentModule = $_GET['id'];
        }

        if (isset($_POST['upload']) || isset($_FILES['file'])) {
            if (isset($_FILES['file'])) {
                $this->filesRepo->upload($_FILES, 'project', session("currentProject"));
                $this->tpl->setNotification('notifications.file_upload_success', 'success', "file_uploaded");
            } else {
                $this->tpl->setNotification('notifications.file_upload_error', 'error');
            }
        }

        if (isset($_GET['delFile']) === true) {
            $result = $this->filesService->deleteFile($_GET['delFile']);

            if ($result === true) {
                $this->tpl->setNotification($this->language->__("notifications.file_deleted"), "success", "file_deleted");
                return Frontcontroller::redirect(BASE_URL . "/files/showAll" . ($_GET['modalPopUp'] ?? '') ? "?modalPopUp=true" : "");
            } else {
                $this->tpl->setNotification($result["msg"], "success");
            }
        }

        $this->tpl->assign('currentModule', $currentModule);
        $this->tpl->assign('modules', $this->filesRepo->getModules(session("userdata.id")));
        $this->tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
        $this->tpl->assign('files', $this->filesRepo->getFilesByModule("project", session("currentProject"), session("userdata.id")));
        return $this->tpl->displayPartial('files.showAll');
    }
}
