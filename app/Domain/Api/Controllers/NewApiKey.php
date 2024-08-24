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

/**
 *
 */
class NewApiKey extends Controller
{
    private UserRepository $userRepo;
    private ProjectRepository $projectsRepo;
    private UserService $userService;
    private ApiService $APIService;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param UserRepository    $userRepo
     * @param ProjectRepository $projectsRepo
     * @param UserService       $userService
     * @param ApiService        $APIService
     *
     * @return void
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
     * run - display template and edit data
     *
     * @access public
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $values = array(
            'firstname' => "",
            'lastname' => "",
            'user' => "",
            'role' => "",
            'password' => "",
            'status' => 'a',
            'source' => 'api',
        );

        //only Admins
        if (Auth::userIsAtLeast(Roles::$admin)) {
            $projectRelation = array();

            if (isset($_POST['save'])) {
                $values = array(
                    'firstname' => ($_POST['firstname']),
                    'user' => '',
                    'role' => ($_POST['role']),
                    'password' => '',
                    'pwReset' => '',
                    'status' => '',
                    'source' => 'api',
                );

                if (isset($_POST['projects']) && is_array($_POST['projects'])) {
                    foreach ($_POST['projects'] as $project) {
                        $projectRelation[] = $project;
                    }
                }

                $apiKeyValues = $this->APIService->createAPIKey($values);

                //Update Project Relationships
                if (isset($_POST['projects']) && count($_POST['projects']) > 0) {
                    if ($_POST['projects'][0] !== '0') {
                        $this->projectsRepo->editUserProjectRelations($apiKeyValues['id'], $_POST['projects']);
                    } else {
                        $this->projectsRepo->deleteAllProjectRelations($apiKeyValues['id']);
                    }
                }

                $this->tpl->setNotification("notification.api_key_created", 'success', 'apikey_created');

                $this->tpl->assign('apiKeyValues', $apiKeyValues);
            }

            $this->tpl->assign('values', $values);

            $this->tpl->assign('allProjects', $this->projectsRepo->getAll());
            $this->tpl->assign('roles', Roles::getRoles());

            $this->tpl->assign('relations', $projectRelation);

            return $this->tpl->displayPartial('api.newAPIKey');
        } else {
            return $this->tpl->displayPartial('errors.error403');
        }
    }
}
