<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class Users extends Controller
{
    private UserService $userService;

    private ApiService $apiService;

    /**
     * init - initialize private variables
     */
    public function init(
        UserService $userService,
        ApiService $apiService
    ): void {
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
        if (isset($params['projectUsersAccess'])) {
            if ($params['projectUsersAccess'] == 'current') {
                $projectId = session('currentProject');
            } else {
                $projectId = $params['projectUsersAccess'];
            }

            // Pass projectId by name so we don't depend on positional
            // order and so the optional $currentUser parameter resolves
            // to the session user inside the service per its docblock.
            $users = $this->userService->getUsersWithProjectAccess(projectId: $projectId);

            if (isset($params['query'])) {
                $users = $this->apiService->filterUsersByQuery($users, $params['query']);
            }

            return $this->tpl->displayJson($users);
        }

        if (isset($params['profileImage'])) {

            if ($params['profileImage'] === 'me') {
                $params['profileImage'] = session('userdata.id');
            }
            $image = $this->userService->getProfilePicture($params['profileImage']);

            return $this->apiService->buildImageResponse($image);
        }

        return $this->tpl->displayJson(['status' => 'failure'], 500);
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
        if (! isset($_FILES['file'])) {
            return $this->tpl->displayJson(['error' => 'File not included'], 400);
        }

        // Updating User Image
        $_FILES['file']['name'] = 'userPicture.png';

        $this->userService->setProfilePicture($_FILES, session('userdata.id'));

        session(['msg' => 'PICTURE_CHANGED']);
        session(['msgT' => 'success']);

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * put - Special handling for settings
     *
     * @param  array  $params  parameters or body of the request
     */
    public function patch(array $params): Response
    {
        if (
            count(array_intersect(array_keys($params), ['patchModalSettings', 'patchViewSettings', 'patchMenuStateSettings'])) == 0
            || (! empty($params['patchModalSettings']) && empty($params['settings']))
            || (! empty($params['patchViewSettings']) && empty($params['value']))
            || (! empty($params['patchMenuStateSettings']) && empty($params['value']))
        ) {
            return $this->tpl->displayJson(['status' => 'failure', 'error' => 'Required params not included in request'], 400);
        }

        $success = false;
        foreach (
            [
                'patchModalSettings' => fn () => $this->userService->updateUserSettings('modals', $params['settings'], 1),
                'patchViewSettings' => fn () => $this->userService->updateUserSettings('views', $params['patchViewSettings'], $params['value']),
                'patchMenuStateSettings' => fn () => $this->userService->updateUserSettings('views', 'menuState', $params['value']),
            ] as $param => $callback
        ) {
            if (! isset($params[$param])) {
                continue;
            }

            $success = $callback();
            break;
        }

        if ($success) {
            return $this->tpl->displayJson(['status' => 'ok']);
        }

        return $this->tpl->displayJson(['status' => 'failure'], 500);
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
