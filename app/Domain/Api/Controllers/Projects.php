<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Files\Fileupload as FileuploadCore;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class Projects extends Controller
{
    private FileuploadCore $fileUpload;

    private ProjectService $projectService;

    private FileRepository $filesRepository;

    private UserService $userService;

    /**
     * init - initialize private variables
     */
    public function init(
        FileuploadCore $fileUpload,
        ProjectService $projectService,
        FileRepository $filesRepository,
        UserService $userService,
    ): void {
        $this->fileUpload = $fileUpload;
        $this->projectService = $projectService;
        $this->filesRepository = $filesRepository;
        $this->userService = $userService;
    }

    /**
     * get - handle get requests
     *
     *
     * @param  array  $params  parameters or body of the request
     *
     * @throws BindingResolutionException
     */
    public function get(array $params): Response
    {
        if (! isset($params['projectAvatar'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        $svg = $this->projectService->getProjectAvatar($params['projectAvatar']);
        if (is_array($svg)) {
            $file = $this->fileUpload;

            return match ($svg['type']) {
                'uploaded' => $file->displayImageFile($svg['filename']),
                'generated' => $file->displayImageFile('avatar', $svg['filename'], true),
            };
        }

        $response = new Response($svg->toXMLString());
        $response->headers->set('Content-type', 'image/svg+xml');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'max-age=86400');

        return $response;
    }

    /**
     * post - handle post requests
     *
     *
     * @param  array  $params  parameters or body of the request
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        // Updating User Image
        if (! empty($_FILES['file'])) {
            $_FILES['file']['name'] = 'profileImage-'.session('currentProject').'.png';

            $this->projectService->setProjectAvatar($_FILES, session('currentProject'));

            session(['msg' => 'PICTURE_CHANGED']);
            session(['msgT' => 'success']);

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        if (! isset($params['action'], $params['payload'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        $callback = match ($params['action']) {
            'sortIndex' => fn () => $this->projectService->updateProjectStatusAndSorting($params['payload'], $handler ?? null),
            'ganttSort' => fn () => $this->projectService->updateProjectSorting($params['payload']),
        };

        if (! $callback()) {
            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * put - Special handling for settings
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function patch(array $params): Response
    {

        foreach (
            [
                'id' => fn () => $this->projectService->patch($params['id'], $params),
                'patchModalSettings' => fn () => $this->userService->updateUserSettings('modals', $params['settings'], 1),
                'patchViewSettings' => fn () => $this->userService->updateUserSettings('views', $params['patchViewSettings'], $params['value']),
                'patchMenuStateSettings' => fn () => $this->userService->updateUserSettings('views', 'menuState', $params['value']),
                'patchProjectProgress' => fn () => $this->projectService->updateProjectProgress($params['values'], session('currentProject')),
            ] as $param => $callback
        ) {
            if (! isset($params[$param])) {
                continue;
            }

            if (! $callback()) {
                return $this->tpl->displayJson(['status' => 'failure', 'error' => 'Something went wrong'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        return new Response;
    }

    /**
     * delete - handle delete requests
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}
