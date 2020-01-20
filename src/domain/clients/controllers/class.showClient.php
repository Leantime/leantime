<?php

/**
 * showClient Class - Show one client
 *
 */


namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class showClient
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $clientRepo = new repositories\clients();

            $id = '';

            if (isset($_GET['id']) === true) {
                $id = (int)($_GET['id']);
            }



            $row = $clientRepo->getClient($id);

            $msgKey = '';

            $clientValues = array(
                'name' => $row['name'],
                'street' => $row['street'],
                'zip' => $row['zip'],
                'city' => $row['city'],
                'state' => $row['state'],
                'country' => $row['country'],
                'phone' => $row['phone'],
                'internet' => $row['internet'],
                'email' => $row['email']
            );


            if (empty($row) === false) {

                $file = new repositories\files();
                $project = new repositories\projects();

                $msgKey = '';
                if ($_SESSION['userdata']['role'] == 'admin') {
                    $tpl->assign('admin', true);
                }

                if (isset($_POST['upload'])) {

                    if (isset($_FILES['file']) === true && $_FILES['file']["tmp_name"] != "") {
                        $msgKey = $file->upload($_FILES, 'client', $id);
                    }else{
                        $tpl->setNotification('No File specified', 'error');
                    }
                }
                $comment = new repositories\comments();

                //Add comment
                if (isset($_POST['comment']) === true) {

                    $mail = new core\mailer();
                    $values = array(
                        'text' => $_POST['text'],
                        'date' => date("Y-m-d H:i:s"),
                        'userId' => ($_SESSION['userdata']['id']),
                        'moduleId' => $id,
                        'commentParent' => ($_POST['father'])
                    );

                    $comment->addComment($values, 'client');
                }

                if (isset($_POST['save']) === true) {

                    $clientValues = array(
                        'name' => $_POST['name'],
                        'street' => $_POST['street'],
                        'zip' => $_POST['zip'],
                        'city' => $_POST['city'],
                        'state' => $_POST['state'],
                        'country' => $_POST['country'],
                        'phone' => $_POST['phone'],
                        'internet' => $_POST['internet'],
                        'email' => $_POST['email']
                    );

                    if ($clientValues['name'] !== '') {

                        $clientRepo->editClient($clientValues, $id);

                        $tpl->setNotification('EDIT_CLIENT_SUCCESS', 'success');

                    } else {

                        $tpl->setNotification('NO_NAME', 'error');
                    }
                }


                $tpl->assign('userClients', $clientRepo->getClientsUsers($id));
                $tpl->assign('comments', $comment->getComments('client', $id));
                $tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
                $tpl->assign('info', $msgKey);
                $tpl->assign('client', $clientValues);
                $tpl->assign('users', new repositories\users());
                $tpl->assign('clientProjects', $project->getClientProjects($id));
                $tpl->assign('files', $file->getFilesByModule('client'));
                $tpl->assign('helper', new core\helper());
                //var_dump($file->getFilesByModule('client')); die();

                $tpl->display('clients.showClient');

            } else {

                $tpl->display('general.error');

            }


        }

    }
}
