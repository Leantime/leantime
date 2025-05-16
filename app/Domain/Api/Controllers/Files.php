<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class Files extends Controller
{
    private UserService $userService;

    private FileService $fileService;

    /**
     * init - initialize private variables
     */
    public function init(FileService $fileService, UserService $userService): void
    {
        $this->userService = $userService;
        $this->fileService = $fileService;
    }

    /**
     * get - handle get requests
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * post - handle post requests
     *
     *
     *
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function post(array $params): Response
    {
        // FileUpload
        if (isset($_FILES['file']) && isset($_GET['module']) && isset($_GET['moduleId'])) {
            $module = htmlentities($_GET['module']);
            $id = (int) $_GET['moduleId'];

            $result = $this->fileService->upload($_FILES, $module, $id);
            if (is_string($result)) {
                return $this->tpl->displayJson(['status' => 'error', 'message' => $result], 500);
            } else {
                return $this->tpl->displayJson($result);
            }
        }

        if (isset($_FILES['file'])) {
            // For image paste uploads
            $_FILES['file']['name'] = 'pastedImage.png';
            $file = $this->fileService->upload($_FILES, 'project', session('currentProject'));

            if (is_array($file)) {
                return new Response(BASE_URL.'/files/get?'
                    .http_build_query([
                        'encName' => $file['encName'],
                        'ext' => $file['extension'],
                        'realName' => $file['realName'],
                    ]));
            }

            if (is_string($file)) {
                // If the result is a string, it's an error message
                $this->tpl->displayJson(['status' => 'error', 'message' => $file], 500);
            }
        }

        return $this->tpl->displayJson(['status' => 'Something unexpected'], 500);
    }

    /**
     * put - handle put requests
     */
    public function patch(array $params): Response
    {
        if (
            ! isset($params['patchModalSettings'])
            || ! $this->userService->updateUserSettings('modals', $params['settings'], 1)
        ) {
            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * delete - handle delete requests
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}
