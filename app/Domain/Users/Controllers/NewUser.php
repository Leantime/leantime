<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Users\Permissions\UsersPermissions;
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
    #[RequiresPermission(UsersPermissions::CREATE, global: true)]
    public function get(array $params): Response
    {
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
    #[RequiresPermission(UsersPermissions::CREATE, global: true)]
    public function post(array $params): Response
    {
        $values = $this->getDefaultValues();
        $projectrelation = [];

        if (isset($_POST['save'])) {
            $isManager = Auth::userHasRole(Roles::$manager);

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
                'clientId' => $isManager ? session('userdata.clientId') : $_POST['client'],
            ];

            if (isset($_POST['projects']) && is_array($_POST['projects'])) {
                foreach ($_POST['projects'] as $project) {
                    $projectrelation[] = $project;
                }
            }

            $result = $this->userService->inviteNewUser($_POST, session('userdata.clientId'), $isManager);

            if ($result === 'enter_email') {
                $this->tpl->setNotification($this->language->__('notification.enter_email'), 'error');
            } elseif ($result === 'no_valid_email') {
                $this->tpl->setNotification($this->language->__('notification.no_valid_email'), 'error');
            } elseif ($result === 'user_exists') {
                $this->tpl->setNotification($this->language->__('notification.user_exists'), 'error');
            } else {
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
