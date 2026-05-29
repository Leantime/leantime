<?php

namespace Leantime\Domain\Clients\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Files\Services\Files as FileService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowClient Controller - Show one client.
 */
class ShowClient extends Controller
{
    private ClientService $clientService;

    private CommentService $commentService;

    private FileService $fileService;

    /**
     * Initializes dependencies.
     */
    public function init(
        ClientService $clientService,
        CommentService $commentService,
        FileService $fileService
    ): void {
        $this->clientService = $clientService;
        $this->commentService = $commentService;
        $this->fileService = $fileService;

        if (! session()->exists('lastPage')) {
            session(['lastPage' => BASE_URL.'/clients/showAll']);
        }
    }

    /**
     * Displays the client detail page.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        $id = (int) $params['id'];
        $client = $this->clientService->get($id);

        if ($client === false) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        // Handle file deletion via GET param
        if (isset($_GET['delFile'])) {
            $result = $this->fileService->deleteFile($_GET['delFile']);

            if ($result === true) {
                $this->tpl->setNotification($this->language->__('notifications.file_deleted'), 'success', 'clientfile_deleted');

                return Frontcontroller::redirect(BASE_URL.'/clients/showClient/'.$id.'#files');
            } else {
                $this->tpl->setNotification($result['msg'], 'error');
            }
        }

        if (session('userdata.role') == 'admin') {
            $this->tpl->assign('admin', true);
        }

        $this->tpl->assign('client', $client);

        $pageData = $this->clientService->getClientPageData($id);
        array_map([$this->tpl, 'assign'], array_keys($pageData), array_values($pageData));

        return $this->tpl->display('clients.showClient');
    }

    /**
     * Handles client detail form submissions (save, upload, comment).
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        $id = (int) $params['id'];
        $client = $this->clientService->get($id);

        if ($client === false || ! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        // Handle file upload
        if (isset($_POST['upload'])) {
            if (isset($_FILES['file']) && $_FILES['file']['tmp_name'] != '') {
                $this->fileService->upload($_FILES, 'client', $id);
                $this->tpl->setNotification($this->language->__('notifications.file_upload_success'), 'success', 'clientfile_uploaded');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.file_upload_error'), 'error');
            }
        }

        // Handle comment
        if (isset($_POST['comment'])) {
            if ($this->commentService->addComment($_POST, 'client', $id, $client)) {
                $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.comment_create_error'), 'error');
            }
        }

        // Handle client save
        if (isset($_POST['save'])) {
            $values = [
                'id' => $id,
                'name' => $_POST['name'] ?? '',
                'street' => $_POST['street'] ?? '',
                'zip' => $_POST['zip'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'country' => $_POST['country'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'internet' => $_POST['internet'] ?? '',
                'email' => $_POST['email'] ?? '',
            ];

            try {
                $this->clientService->updateClient($values);
                $this->tpl->setNotification($this->language->__('notification.client_saved_successfully'), 'success');
            } catch (MissingParameterException) {
                $this->tpl->setNotification($this->language->__('notification.client_name_not_specified'), 'error');
            }

            $client = $values;
        }

        if (session('userdata.role') == 'admin') {
            $this->tpl->assign('admin', true);
        }

        $this->tpl->assign('client', $client);

        $pageData = $this->clientService->getClientPageData($id);
        array_map([$this->tpl, 'assign'], array_keys($pageData), array_values($pageData));

        return $this->tpl->display('clients.showClient');
    }
}
