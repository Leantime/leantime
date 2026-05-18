<?php

/**
 * showClient Class - Show one client
 */

namespace Leantime\Domain\Clients\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Users\Services\Users as UserService;

class ShowClient extends Controller
{
    private ClientRepository $clientRepo;

    private CommentService $commentService;

    private FileService $fileService;

    private ProjectRepository $projectRepo;

    private UserRepository $userRepo;

    private UserService $userService;

    /**
     * init - initialize private variables
     */
    public function init(
        ClientRepository $clientRepo,
        CommentService $commentService,
        FileService $fileService,
        ProjectRepository $projectRepo,
        UserRepository $userRepo,
        UserService $userService
    ): void {
        $this->clientRepo = $clientRepo;
        $this->commentService = $commentService;
        $this->fileService = $fileService;
        $this->projectRepo = $projectRepo;
        $this->userRepo = $userRepo;
        $this->userService = $userService;

        if (! session()->exists('lastPage')) {
            session(['lastPage' => BASE_URL.'/clients/showAll']);
        }
    }

    /**
     * run - display template and edit data
     */
    public function run()
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $row = $this->clientRepo->getClient($id);

        if ($row === false) {
            $this->tpl->display('errors.error404');

            return;
        }

        $clientValues = [
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
        ];

        if (! empty($row) && Auth::userIsAtLeast(Roles::$admin)) {

            if (session('userdata.role') == 'admin') {
                $this->tpl->assign('admin', true);
            }

            // File upload
            if (isset($_POST['upload'])) {
                if (isset($_FILES['file']) && $_FILES['file']['tmp_name'] != '') {
                    $this->fileService->upload($_FILES, 'client', $id);
                    $this->tpl->setNotification($this->language->__('notifications.file_upload_success'), 'success', 'clientfile_uploaded');
                } else {
                    $this->tpl->setNotification($this->language->__('notifications.file_upload_error'), 'error');
                }
            }

            // Delete file
            if (isset($_GET['delFile'])) {
                $result = $this->fileService->deleteFile($_GET['delFile']);

                if ($result === true) {
                    $this->tpl->setNotification($this->language->__('notifications.file_deleted'), 'success', 'clientfile_deleted');

                    return Frontcontroller::redirect(BASE_URL.'/clients/showClient/'.$id.'#files');
                } else {
                    $msg = is_array($result) ? ($result['msg'] ?? '') : '';
                    $this->tpl->setNotification($msg, 'error');
                }
            }

            // Add comment
            if (isset($_POST['comment'])) {
                if ($this->commentService->addComment($_POST, 'client', $id, $row)) {
                    $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');
                } else {
                    $this->tpl->setNotification($this->language->__('notifications.comment_create_error'), 'error');
                }
            }

            // Save client details
            if (isset($_POST['save'])) {
                $clientValues = [
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
                ];

                if ($clientValues['name'] !== '') {
                    $this->clientRepo->editClient($clientValues, $id);
                    $this->tpl->setNotification($this->language->__('notification.client_saved_successfully'), 'success');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.client_name_not_specified'), 'error');
                }
            }

            // Delete a client portal user — only admins+, only role=10 users
            // that actually belong to this client org (defence-in-depth so a
            // crafted POST can't be used to delete arbitrary users).
            if (isset($_POST['deletePortalUser'])) {
                $delUserId = (int) ($_POST['userId'] ?? 0);
                $delUser = $delUserId > 0 ? $this->userRepo->getUser($delUserId) : null;

                if ($delUser
                    && (int) ($delUser['role'] ?? 0) === 10
                    && (int) ($delUser['clientId'] ?? 0) === $id
                ) {
                    $this->userService->deleteUser($delUserId);
                    $this->tpl->setNotification($this->language->__('notifications.user_deleted'), 'success', 'user_deleted');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.cannot_delete_user'), 'error');
                }
            }

            // Save project-to-client-org assignments
            if (isset($_POST['saveProjects'])) {
                $selectedProjectIds = array_map('intval', $_POST['clientProjects'] ?? []);
                $this->projectRepo->setClientProjects($id, $selectedProjectIds);
                $this->tpl->setNotification($this->language->__('notification.client_saved_successfully'), 'success');
            }

            $this->tpl->assign('portalUsers', $this->clientRepo->getClientPortalUsers($id));
            $this->tpl->assign('comments', $this->commentService->getComments('client', $id));
            $this->tpl->assign('imgExtensions', ['jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv']);
            $this->tpl->assign('client', $clientValues);
            $this->tpl->assign('clientProjects', $this->projectRepo->getClientProjects($id));
            $this->tpl->assign('allProjects', $this->projectRepo->getAll());
            $this->tpl->assign('files', $this->fileService->getFilesByModule('client', $id));

            return $this->tpl->display('clients.showClient');
        } else {
            return $this->tpl->display('errors.error403');
        }
    }
}
