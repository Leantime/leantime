<?php

/**
 * showClient Class - Show one client
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showClient extends controller
    {
        private repositories\clients $clientRepo;
        private repositories\setting $settingsRepo;
        private services\projects $projectService;
        private services\comments $commentService;
        private services\files $fileService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            repositories\clients $clientRepo,
            repositories\setting $settingsRepo,
            services\projects $projectService,
            services\comments $commentService,
            services\files $fileService
        ) {
            $this->clientRepo = $clientRepo;
            $this->settingsRepo = $settingsRepo;
            $this->projectService = $projectService;
            $this->commentService = $commentService;
            $this->fileService = $fileService;

            if (!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = BASE_URL . "/clients/showAll";
            }
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager], true);

            $id = '';

            if (isset($_GET['id']) === true) {
                $id = (int)($_GET['id']);
            }

            $row = $this->clientRepo->getClient($id);

            $clientValues = array(
                'id' => $row['id'],
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

            if (empty($row) === false && auth::userIsAtLeast(roles::$admin)) {
                $file = app()->make(repositories\files::class);
                $project = app()->maike(repositories\projects::class);

                if ($_SESSION['userdata']['role'] == 'admin') {
                    $this->tpl->assign('admin', true);
                }

                if (isset($_POST['upload'])) {
                    if (isset($_FILES['file']) === true && $_FILES['file']["tmp_name"] != "") {
                        $return = $file->upload($_FILES, 'client', $id);
                        $this->tpl->setNotification($this->language->__("notifications.file_upload_success"), 'success');
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.file_upload_error"), 'error');
                    }
                }

                //Delete File
                if (isset($_GET['delFile']) === true) {
                    $result = $this->fileService->deleteFile($_GET['delFile']);

                    if ($result === true) {
                        $this->tpl->setNotification($this->language->__("notifications.file_deleted"), "success");
                        $this->tpl->redirect(BASE_URL . "/clients/showClient/" . $id . "#files");
                    } else {
                        $this->tpl->setNotification($result["msg"], "success");
                    }
                }


                //Add comment
                if (isset($_POST['comment']) === true) {
                    if ($this->commentService->addComment($_POST, "client", $id, $row)) {
                        $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                    }
                }

                if (isset($_POST['save']) === true) {
                    $clientValues = array(
                        'id' => $row['id'],
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
                        $this->clientRepo->editClient($clientValues, $id);

                        $this->tpl->setNotification($this->language->__("notification.client_saved_successfully"), 'success');
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.client_name_not_specified"), 'error');
                    }
                }

                $this->tpl->assign('userClients', $this->clientRepo->getClientsUsers($id));
                $this->tpl->assign('comments', $this->commentService->getComments('client', $id));
                $this->tpl->assign('imgExtensions', array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'));
                $this->tpl->assign('client', $clientValues);
                $this->tpl->assign('users', app()->make(repositories\users::class));
                $this->tpl->assign('clientProjects', $project->getClientProjects($id));
                $this->tpl->assign('files', $file->getFilesByModule('client', $id));


                $this->tpl->display('clients.showClient');
            } else {
                $this->tpl->display('errors.error403');
            }
        }
    }
}
