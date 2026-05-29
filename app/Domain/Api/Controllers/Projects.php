<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class Projects extends Controller
{
    private ProjectService $projectService;

    private UserService $userService;

    private ApiService $apiService;

    /**
     * init - initialize private variables
     */
    public function init(
        ProjectService $projectService,
        UserService $userService,
        ApiService $apiService,
    ): void {
        $this->projectService = $projectService;
        $this->userService = $userService;
        $this->apiService = $apiService;
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

        $image = $this->projectService->getProjectAvatar($params['projectAvatar']);

        return $this->apiService->buildImageResponse($image);
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
            'sortIndex' => fn () => $this->projectService->updateProjectStatusAndSorting($params['payload'], null),
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
