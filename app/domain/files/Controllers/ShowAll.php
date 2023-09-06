<?php

namespace Leantime\Domain\Files\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Files\Services\Files as FileService;
class ShowAll extends Controller
{
    private FileRepository $filesRepo;
    private FileService $filesService;

    public function init(
        FileRepository $filesRepo,
        FileService $filesService
    ) {
        $this->filesRepo = $filesRepo;
        $this->filesService = $filesService;
    }

    public function run()
    {

        $currentModule = '';
        if (isset($_GET['id'])) {
            $currentModule = $_GET['id'];
        }


        if (isset($_POST['upload']) || isset($_FILES['file'])) {
            if (isset($_FILES['file'])) {
                $this->filesRepo->upload($_FILES, 'project', $_SESSION['currentProject']);
                $this->tpl->setNotification('notifications.file_upload_success', 'success');
            } else {
                $this->tpl->setNotification('notifications.file_upload_error', 'error');
            }
        }

        if (isset($_GET['delFile']) === true) {
            $result = $this->filesService->deleteFile($_GET['delFile']);

            if ($result === true) {
                $this->tpl->setNotification($this->language->__("notifications.file_deleted"), "success");
                $this->tpl->redirect(BASE_URL . "/files/showAll" . ($_GET['modalPopUp'] ?? '') ? "?modalPopUp=true" : "");
            } else {
                $this->tpl->setNotification($result["msg"], "success");
            }
        }

        $this->tpl->assign('currentModule', $currentModule);
        $this->tpl->assign('modules', $this->filesRepo->getModules($_SESSION['userdata']['id']));
        $this->tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
        $this->tpl->assign('files', $this->filesRepo->getFilesByModule("project", $_SESSION['currentProject'], $_SESSION['userdata']['id']));
        $this->tpl->displayPartial('files.showAll');
    }
}
