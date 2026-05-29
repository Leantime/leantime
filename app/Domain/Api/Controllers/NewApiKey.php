<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class NewApiKey extends Controller
{
    private UserRepository $userRepo;

    private ProjectRepository $projectsRepo;

    private UserService $userService;

    private ApiService $APIService;

    /**
     * Initializes dependencies.
     *
     * @throws BindingResolutionException
     */
    public function init(
        UserRepository $userRepo,
        ProjectRepository $projectsRepo,
        UserService $userService,
        ApiService $APIService
    ): void {
        self::dispatch_event('api_key_init', $this);

        $this->userRepo = $userRepo;
        $this->projectsRepo = $projectsRepo;
        $this->userService = $userService;
        $this->APIService = $APIService;
    }

    /**
     * Displays the new API key form.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $values = [
            'firstname' => '',
            'lastname' => '',
            'user' => '',
            'role' => '',
            'password' => '',
            'status' => 'a',
            'source' => 'api',
        ];

        $this->tpl->assign('values', $values);
        $this->tpl->assign('allProjects', $this->projectsRepo->getAll());
        $this->tpl->assign('roles', Roles::getRoles());
        $this->tpl->assign('relations', []);

        return $this->tpl->displayPartial('api.newAPIKey');
    }

    /**
     * Handles API key creation.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $values = [
            'firstname' => '',
            'lastname' => '',
            'user' => '',
            'role' => '',
            'password' => '',
            'status' => 'a',
            'source' => 'api',
        ];

        $projectRelation = [];

        if (isset($_POST['save'])) {
            $values = [
                'firstname' => ($_POST['firstname']),
                'user' => '',
                'role' => ($_POST['role']),
                'password' => '',
                'pwReset' => '',
                'status' => '',
                'source' => 'api',
            ];

            if (isset($_POST['projects']) && is_array($_POST['projects'])) {
                foreach ($_POST['projects'] as $project) {
                    $projectRelation[] = $project;
                }
            }

            $apiKeyValues = $this->APIService->createAPIKey($values);

            if (isset($_POST['projects']) && count($_POST['projects']) > 0) {
                if ($_POST['projects'][0] !== '0') {
                    $this->projectsRepo->editUserProjectRelations($apiKeyValues['id'], $_POST['projects']);
                } else {
                    $this->projectsRepo->deleteAllProjectRelations($apiKeyValues['id']);
                }
            }

            $this->tpl->setNotification('notifications.key_created', 'success', 'apikey_created');
            $this->tpl->assign('apiKeyValues', $apiKeyValues);
        }

        $this->tpl->assign('values', $values);
        $this->tpl->assign('allProjects', $this->projectsRepo->getAll());
        $this->tpl->assign('roles', Roles::getRoles());
        $this->tpl->assign('relations', $projectRelation);

        return $this->tpl->displayPartial('api.newAPIKey');
    }
}
