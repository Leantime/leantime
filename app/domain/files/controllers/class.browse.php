<?php

namespace leantime\domain\controllers;

use leantime\core;
use leantime\core\controller;
use leantime\domain\repositories;
use leantime\domain\services;

class browse extends controller
{
    public function init()
    {
        $this->filesRepo = new repositories\files();
        $this->filesService = new services\files();
    }

    public function run()
    {


        $currentModule = $_SESSION['currentProject'];


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
                $this->tpl->redirect(BASE_URL . "/files/showAll" . ($_GET['modalPopUp']??'') ? "?modalPopUp=true" : "");
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
