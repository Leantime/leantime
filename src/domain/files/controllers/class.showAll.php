<?php

namespace leantime\domain\controllers;

use leantime\core;
use leantime\domain\repositories;
use leantime\domain\services;

class showAll extends repositories\files
{

    public function run()
    {

        $tpl = new core\template();
        $fileService = new services\files();
        $language = new core\language();

        $currentModule = '';
        if (isset($_GET['id'])) {
            $currentModule = $_GET['id'];
        }


        if (isset($_POST['upload']) || isset($_FILES['file'])) {

            if (isset($_FILES['file'])) {

                $this->upload($_FILES, 'project', $_SESSION['currentProject']);
                $tpl->setNotification('notifications.file_upload_success', 'success');

            } else {

                $tpl->setNotification('notifications.file_upload_error', 'error');

            }
        }

        if (isset($_GET['delFile']) === true) {

            $result = $fileService->deleteFile($_GET['delFile']);

            if($result === true) {
                $tpl->setNotification($language->__("notifications.file_deleted"), "success");
                $tpl->redirect(BASE_URL."/files/showAll".($_GET['modalPopUp']) ? "?modalPopUp=true" : "");
            }else {
                $tpl->setNotification($result["msg"], "success");
            }

        }

        $tpl->assign('folders', $this->getFolders($currentModule));
        $tpl->assign('currentModule', $currentModule);
        $tpl->assign('modules', $this->getModules($_SESSION['userdata']['id']));
        $tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
        $tpl->assign('files', $this->getFilesByModule($currentModule, null, $_SESSION['userdata']['id']));
        $tpl->displayPartial('files.showAll');
    }

}
