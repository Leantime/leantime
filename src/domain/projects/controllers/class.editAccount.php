<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class editAccount
    {

        public function run()
        {

            $tpl = new core\template();
            $projectRepo = new repositories\projects();
            $id = (int)$_GET['id'];

            if ($id > 0) {

                $account = $projectRepo->getProjectAccount($id);
                $values = array(
                    'name' => $_POST['name'],
                    'username' => $_POST['username'],
                    'password' => $_POST['password'],
                    'host' => $_POST['host'],
                    'kind' => $_POST['kind']
                );

                if (isset($_POST['accountSubmit'])) {
                    $values = array(
                        'name' => $_POST['accountName'],
                        'username' => $_POST['username'],
                        'password' => $_POST['password'],
                        'host' => $_POST['host'],
                        'kind' => $_POST['kind']
                    );

                    $projectRepo->addAccount($values, $id);
                } else {

                    $tpl->setNotification('MISSING_FIELDS', 'error');
                }

            } else {

                $tpl->display('general.error');
            }

            $tpl->assign('account', $values);
            $tpl->display('projects.editAccount');

        }

    }

}
