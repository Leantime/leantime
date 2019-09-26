<?php

namespace leantime\domain\controllers;

use leantime\core;
use leantime\domain\repositories;

class addWidget extends repositories\dashboard
{

    public function run()
    {

        $tpl = new core\template();

        if (isset($_POST['save'])) {
            if (isset($_POST['title']) && isset($_POST['submoduleAlias'])) {

                $this->addWidget($_POST['submoduleAlias'], $_POST['title']);
                $tpl->setNotification('SAVE_SUCCESS', 'success');

            } else {

                $tpl->setNotification('MISSING_FIELDS', 'error');

            }
        }

        $setting = new repositories\setting();
        $tpl->assign('submodules', $setting->getAllSubmodules());
        $tpl->display('dashboard.addWidget');
    }

}

?>
