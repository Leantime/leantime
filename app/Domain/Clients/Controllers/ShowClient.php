<?php

/**
 * showClient Class - Show one client
 *
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Files\Services\Files as FileService;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;

    /**
     *
     */
    class ShowClient extends Controller
    {
        private ClientRepository $clientRepo;
        private SettingRepository $settingsRepo;
        private ProjectService $projectService;
        private CommentService $commentService;
        private FileService $fileService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            ClientRepository $clientRepo,
            SettingRepository $settingsRepo,
            ProjectService $projectService,
            CommentService $commentService,
            FileService $fileService
        ) {
            $this->clientRepo = $clientRepo;
            $this->settingsRepo = $settingsRepo;
            $this->projectService = $projectService;
            $this->commentService = $commentService;
            $this->fileService = $fileService;

            if (!session()->exists("lastPage")) {
                session(["lastPage" => BASE_URL . "/clients/showAll"]);
            }
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

            $id = '';

            if (isset($_GET['id']) === true) {
                $id = (int)($_GET['id']);
            }

            $row = $this->clientRepo->getClient($id);

            if ($row === false) {
                $this->tpl->display('errors.error404');
                return;
            }

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
                'email' => $row['email'],
            );

            if (empty($row) === false && Auth::userIsAtLeast(Roles::$admin)) {
                $file = app()->make(FileRepository::class);
                $project = app()->make(ProjectRepository::class);

                if (session("userdata.role") == 'admin') {
                    $this->tpl->assign('admin', true);
                }

                if (isset($_POST['upload'])) {
                    if (isset($_FILES['file']) === true && $_FILES['file']["tmp_name"] != "") {
                        $return = $file->upload($_FILES, 'client', $id);
                        $this->tpl->setNotification($this->language->__("notifications.file_upload_success"), 'success', "clientfile_uploaded");
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.file_upload_error"), 'error');
                    }
                }

                //Delete File
                if (isset($_GET['delFile']) === true) {
                    $result = $this->fileService->deleteFile($_GET['delFile']);

                    if ($result === true) {
                        $this->tpl->setNotification($this->language->__("notifications.file_deleted"), "success", "clientfile_deleted");
                        return Frontcontroller::redirect(BASE_URL . "/clients/showClient/" . $id . "#files");
                    } else {
                        $this->tpl->setNotification($result["msg"], "error");
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
                        'email' => $_POST['email'],
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
                $this->tpl->assign('users', app()->make(UserRepository::class));
                $this->tpl->assign('clientProjects', $project->getClientProjects($id));
                $this->tpl->assign('files', $file->getFilesByModule('client', $id));


                return $this->tpl->display('clients.showClient');
            } else {
                return $this->tpl->display('errors.error403');
            }
        }
    }
}
