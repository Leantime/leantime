<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class NewUser extends Controller
{
    private UserService $userService;

    private ProjectService $projectService;

    private ClientService $clientService;

    /**
     * Initializes dependencies.
     */
    public function init(
        UserService $userService,
        ProjectService $projectService,
        ClientService $clientService
    ): void {
        $this->userService = $userService;
        $this->projectService = $projectService;
        $this->clientService = $clientService;
    }

    /**
     * Displays the new user invitation form.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $projectrelation = [];
        if (isset($params['preSelectProjectId'])) {
            $preSelected = explode(',', $params['preSelectProjectId']);
            foreach ($preSelected as $item) {
                $projectrelation[] = (int) $item;
            }
        }

        $this->tpl->assign('values', $this->getDefaultValues());
        $this->tpl->assign('preSelectedClient', isset($params['preSelectedClient']) ? (int) $params['preSelectedClient'] : '');
        $this->tpl->assign('relations', $projectrelation);
        $this->assignTemplateVars();

        return $this->tpl->displayPartial('users.newUser');
    }

    /**
     * Handles new user creation / invitation.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $values = $this->getDefaultValues();
        $projectrelation = [];

        if (isset($_POST['save'])) {
            $values = [
                'firstname' => $_POST['firstname'],
                'lastname' => $_POST['lastname'],
                'user' => $_POST['user'],
                'phone' => $_POST['phone'],
                'role' => $_POST['role'],
                'password' => '',
                'pwReset' => '',
                'status' => '',
                'jobTitle' => $_POST['jobTitle'],
                'jobLevel' => $_POST['jobLevel'],
                'department' => $_POST['department'],
                'clientId' => Auth::userHasRole(Roles::$manager) ? session('userdata.clientId') : $_POST['client'],
            ];

            if (isset($_POST['projects']) && is_array($_POST['projects'])) {
                foreach ($_POST['projects'] as $project) {
                    $projectrelation[] = $project;
                }
            }

            if ($values['user'] === '') {
                $this->tpl->setNotification($this->language->__('notification.enter_email'), 'error');
            } elseif (! filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
                $this->tpl->setNotification($this->language->__('notification.no_valid_email'), 'error');
            } elseif ($this->userService->usernameExist($values['user'])) {
                $this->tpl->setNotification($this->language->__('notification.user_exists'), 'error');
            } else {
                $userId = $this->userService->createUserInvite($values);

                if (isset($_POST['projects']) && count($_POST['projects']) > 0) {
                    if ($_POST['projects'][0] !== '0') {
                        $this->projectService->editUserProjectRelations($userId, $_POST['projects']);
                    } else {
                        $this->projectService->deleteAllUserProjectRelations($userId);
                    }
                }

                $this->tpl->setNotification('notification.user_invited_successfully', 'success', 'user_invited');
            }
        }

        $this->tpl->assign('values', $values);
        $this->tpl->assign('preSelectedClient', '');
        $this->tpl->assign('relations', $projectrelation);
        $this->assignTemplateVars();

        return $this->tpl->displayPartial('users.newUser');
    }

    /**
     * Returns default empty values for the new user form.
     */
    private function getDefaultValues(): array
    {
        return [
            'firstname' => '',
            'lastname' => '',
            'user' => '',
            'phone' => '',
            'role' => '',
            'password' => '',
            'clientId' => '',
            'jobTitle' => '',
            'jobLevel' => '',
            'department' => '',
        ];
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(): void
    {
        $this->tpl->assign('clients', $this->clientService->getAll());
        $this->tpl->assign('allProjects', $this->projectService->getAll());
        $this->tpl->assign('roles', Roles::getRoles());
    }
}
