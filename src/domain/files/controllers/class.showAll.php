<?php

namespace leantime\domain\controllers;

use leantime\core;
use leantime\domain\repositories;

class showAll extends repositories\files
{

    public function run()
    {

        $tpl = new core\template();

        $currentModule = '';
        if (isset($_GET['id'])) {
            $currentModule = $_GET['id'];
        }


        if (isset($_POST['upload']) || isset($_FILES['file'])) {

            if (isset($_FILES['file'])) {

                $this->upload($_FILES, 'project', $_SESSION['currentProject']);
                $tpl->setNotification('FILE_UPLOADED', 'success');

            } else {

                $tpl->setNotification('NO_FILES', 'error');

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
