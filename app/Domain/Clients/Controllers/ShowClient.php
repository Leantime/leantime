<?php

/**
 * showClient Class - Show one client
 */

namespace Leantime\Domain\Clients\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Models\Clients as ClientModel;
    use Leantime\Domain\Clients\Services\Clients as ClientService;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Files\Services\Files as FileService;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Symfony\Component\HttpFoundation\Response;

    class ShowClient extends Controller
    {
        private ClientService $clientService;

        private SettingRepository $settingsRepo;

        private CommentService $commentService;
        
        private ProjectService $projectService;

        private FileService $fileService;

        /**
         * init - initialize private variables
         */
        public function init(
            ClientService $clientService,
            SettingRepository $settingsRepo,
            CommentService $commentService,
            ProjectService $projectService,
            FileService $fileService
        ) {
            $this->clientService = $clientService;
            $this->settingsRepo = $settingsRepo;
            $this->commentService = $commentService;
            $this->projectService = $projectService;
            $this->fileService = $fileService;

            if (! session()->exists('lastPage')) {
                session(['lastPage' => BASE_URL.'/clients/showAll']);
            }
        }


        public function get($params): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

            $id = '';

            if (isset($params['id']) === true) {
                $id = (int) ($params['id']);
            }

            $row = $this->clientService->get($id);

            if ($row === false) {
                $this->tpl->display('errors.error404');

                return $this->tpl->display('errors.error403');
            }
            
            $clientValues = app() -> make(ClientModel::class, [
                'attributes' => $row
            ]);

            if (empty($row) === false && Auth::userIsAtLeast(Roles::$admin)) {

                if (session('userdata.role') == 'admin') {
                    $this->tpl->assign('admin', true);
                }

                $this->tpl->assign('userClients', $this->clientService->getUserClients($id));
                $this->tpl->assign('comments', $this->commentService->getComments('client', $id));
                $this->tpl->assign('imgExtensions', ['jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv']);
                $this->tpl->assign('client', $clientValues);
                $this->tpl->assign('users', app()->make(UserRepository::class));
                $this->tpl->assign('clientProjects', $this->projectService->getClientProjects($id));
                $this->tpl->assign('files', $this->fileService->getFilesByModule('client', $id));

                return $this->tpl->display('clients.showClient');
            } else {
                return $this->tpl->display('errors.error403');
            }
        }


        /**
         * post - display template and edit data
         */
        public function post($params)
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

            $id = '';

            if (isset($params['id']) === true) {
                $id = (int) ($params['id']);
            }

            $row = $this->clientService->get($id);

            if ($row === false) {
                $this->tpl->display('errors.error404');

                return $this->tpl->display('errors.error404');
            }

            $clientValues = app() -> make(ClientModel::class, [
                'attributes' => $row
            ]);

            if (empty($row) === false && Auth::userIsAtLeast(Roles::$admin)) {
                $file = app()->make(FileRepository::class);

                if (session('userdata.role') == 'admin') {
                    $this->tpl->assign('admin', true);
                }

                if (isset($_POST['upload'])) {
                    if (isset($_FILES['file']) === true && $_FILES['file']['tmp_name'] != '') {
                        $return = $file->upload($_FILES, 'client', $id);
                        $this->tpl->setNotification($this->language->__('notifications.file_upload_success'), 'success', 'clientfile_uploaded');
                    } else {
                        $this->tpl->setNotification($this->language->__('notifications.file_upload_error'), 'error');
                    }
                }

                // Delete File
                if (isset($_GET['delFile']) === true) {
                    $result = $this->fileService->deleteFile($_GET['delFile']);

                    if ($result === true) {
                        $this->tpl->setNotification($this->language->__('notifications.file_deleted'), 'success', 'clientfile_deleted');

                        return Frontcontroller::redirect(BASE_URL.'/clients/showClient/'.$id.'#files');
                    } else {
                        $this->tpl->setNotification($result['msg'], 'error');
                    }
                }

                // Add comment
                if (isset($_POST['comment']) === true) {
                    if ($this->commentService->addComment($_POST, 'client', $id)) {
                        $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');
                    } else {
                        $this->tpl->setNotification($this->language->__('notifications.comment_create_error'), 'error');
                    }
                }

                if (isset($params['save']) === true) {
                    $clientValues = app() -> make(ClientModel::class, [
                        'attributes' => $params
                    ]);

                    if ($clientValues->name !== '') {
                        $this->clientService->editClient($clientValues);

                        $this->tpl->setNotification($this->language->__('notification.client_saved_successfully'), 'success');
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.client_name_not_specified'), 'error');
                    }
                }

                $this->tpl->assign('userClients', $this->clientService->getUserClients($id));
                $this->tpl->assign('comments', $this->commentService->getComments('client', $id));
                $this->tpl->assign('imgExtensions', ['jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv']);
                $this->tpl->assign('client', $clientValues);
                $this->tpl->assign('users', app()->make(UserRepository::class));
                $this->tpl->assign('clientProjects', $this->projectService->getClientProjects($id));
                $this->tpl->assign('files', $this->fileService->getFilesByModule('client', $id));

                return $this->tpl->display('clients.showClient');
            } else {
                return $this->tpl->display('errors.error403');
            }
        }
    }
}
