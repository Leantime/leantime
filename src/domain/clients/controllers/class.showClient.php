<?php

/**
 * showClient Class - Show one client
 *
 */


namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showClient
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function __construct () {
            $this->settingsRepo = new repositories\setting();
            $this->projectService = new services\projects();
            $this->language = new core\language();
            $this->commentService = new services\comments();
            $this->fileService = new services\files();

            if(!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = BASE_URL."/clients/showAll";
            }
        }

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

            if (empty($row) === false && core\login::userIsAtLeast("clientManager")) {

                if(core\login::userHasRole("clientManager") && $id != core\login::getUserClientId()) {
                    $tpl->display('general.error');
                    exit();
                }


                $file = new repositories\files();
                $project = new repositories\projects();

                if ($_SESSION['userdata']['role'] == 'admin') {
                    $tpl->assign('admin', true);
                }

                if (isset($_POST['upload'])) {

                    if (isset($_FILES['file']) === true && $_FILES['file']["tmp_name"] != "") {
                        $return = $file->upload($_FILES, 'client', $id);
                        $tpl->setNotification($this->language->__("notifications.file_upload_success"), 'success');

                    }else{
                        $tpl->setNotification($this->language->__("notifications.file_upload_error"), 'error');
                    }
                }

                //Delete File
                if (isset($_GET['delFile']) === true) {

                    $result = $this->fileService->deleteFile($_GET['delFile']);

                    if($result === true) {
                        $tpl->setNotification($this->language->__("notifications.file_deleted"), "success");
                        $tpl->redirect(BASE_URL."/clients/showClient/".$id."#files");
                    }else {
                        $tpl->setNotification($result["msg"], "success");
                    }

                }


                //Add comment
                if (isset($_POST['comment']) === true) {

                    if($this->commentService->addComment($_POST, "client", $id, $row)) {

                        $tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                    }else {
                        $tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                    }
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

                        $tpl->setNotification($this->language->__("notification.client_saved_successfully"), 'success');

                    } else {

                        $tpl->setNotification($this->language->__("notification.client_name_not_specified"), 'error');
                    }
                }

                $tpl->assign('userClients', $clientRepo->getClientsUsers($id));
                $tpl->assign('comments', $this->commentService->getComments('client', $id));
                $tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
                $tpl->assign('client', $clientValues);
                $tpl->assign('users', new repositories\users());
                $tpl->assign('clientProjects', $project->getClientProjects($id));
                $tpl->assign('files', $file->getFilesByModule('client', $id));
                $tpl->assign('helper', new core\helper());

                $tpl->display('clients.showClient');

            } else {

                $tpl->display('general.error');

            }

        }

    }
}
