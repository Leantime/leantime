<?php

namespace Leantime\Domain\Files\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Files\Services\Files as FileService;

/**
 *
 */
class Browse extends Controller
{
    private FileService $filesService;
    private FileRepository $filesRepo;

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
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        $currentModule = $_SESSION['currentProject'];


        if (isset($_POST['upload']) || isset($_FILES['file'])) {
            if (isset($_FILES['file'])) {
                $this->filesRepo->upload($_FILES, 'project', $_SESSION['currentProject']);
                $this->tpl->setNotification('notifications.file_upload_success', 'success', "file_created");
            } else {
                $this->tpl->setNotification('notifications.file_upload_error', 'error');
            }
        }

        if (isset($_GET['delFile']) === true) {
            $result = $this->filesService->deleteFile($_GET['delFile']);

            if ($result === true) {
                $this->tpl->setNotification($this->language->__("notifications.file_deleted"), "success", "file_deleted");
                $this->tpl->redirect(BASE_URL . "/files/showAll" . ($_GET['modalPopUp'] ?? '') ? "?modalPopUp=true" : "");
            } else {
                $this->tpl->setNotification($result["msg"], "success");
            }
        }

        $this->tpl->assign('currentModule', $currentModule);
        $this->tpl->assign('modules', $this->filesRepo->getModules($_SESSION['userdata']['id']));
        $this->tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv', 'webpe'));
        $this->tpl->assign('files', $this->filesRepo->getFilesByModule("project", $_SESSION['currentProject']));
        $this->tpl->display('files.browse');
    }
}
