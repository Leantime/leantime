<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Files extends Controller
{
    private UserService $userService;
    private FileRepository $fileRepo;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param FileRepository $fileRepo
     * @param UserService    $userService
     *
     * @return void
     */
    public function init(FileRepository $fileRepo, UserService $userService): void
    {
        $this->userService = $userService;
        $this->fileRepo = $fileRepo;
    }

    /**
     * get - handle get requests
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * post - handle post requests
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function post(array $params): Response
    {
        //FileUpload
        if (isset($_FILES['file']) && isset($_GET['module']) && isset($_GET['moduleId'])) {
            $module = htmlentities($_GET['module']);
            $id = (int) $_GET['moduleId'];

            return $this->tpl->displayJson($this->fileRepo->upload($_FILES, $module, $id));
        }

        if (isset($_FILES['file'])) {
            $_FILES['file']['name'] = "pastedImage.png";
            $file = $this->fileRepo->upload($_FILES, 'project', session("currentProject"));

            return new Response(BASE_URL . '/files/get?'
                . http_build_query([
                    'encName' => $file['encName'],
                    'ext' => $file['extension'],
                    'realName' => $file['realName'],
                ]));
        }

        return $this->tpl->displayJson(['status' => 'Something unexpected'], 50);
    }

    /**
     * put - handle put requests
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     */
    public function patch(array $params): Response
    {
        if (
            !isset($params['patchModalSettings'])
            || !$this->userService->updateUserSettings("modals", $params['settings'], 1)
        ) {
            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * delete - handle delete requests
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}
