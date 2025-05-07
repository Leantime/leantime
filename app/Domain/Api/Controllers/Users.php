<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Files\Fileupload as FileuploadCore;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class Users extends Controller
{
    private UserService $userService;

    private FileRepository $filesRepository;

    /**
     * init - initialize private variables
     */
    public function init(
        UserService $userService,
        FileRepository $filesRepository
    ): void {
        $this->userService = $userService;
        $this->filesRepository = $filesRepository;
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

            $users = $this->userService->getUsersWithProjectAccess(session('userdata.id'), $projectId);

            if (isset($params['query'])) {
                $query = $params['query'];
                // Perform a simple filter by query and create a list of the result.
                $users = array_values(
                    array_filter($users, static fn (array $user) => stripos(implode(' ', $user), $query) !== false)
                );
            }

            return $this->tpl->displayJson($users);
        }

        if (isset($params['profileImage'])) {
            if ($params['profileImage'] === 'me') {
                $params['profileImage'] = session('userdata.id');
            }
            $svg = $this->userService->getProfilePicture($params['profileImage']);
            if (is_array($svg)) {
                $file = app()->make(FileuploadCore::class);

                return match ($svg['type']) {
                    'uploaded' => $file->displayImageFile($svg['filename']),
                    'generated' => $file->displayImageFile('avatar', $svg['filename'], true),
                };
            }

            $response = new Response($svg->toXMLString());
            $response->headers->set('Content-type', 'image/svg+xml');

            if (app()->make(Environment::class)->debug === false) {
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'max-age=86400');
            }

            return $response;
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
