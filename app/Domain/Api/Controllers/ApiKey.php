<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * API-key controller.
 */
class ApiKey extends Controller
{
    private ProjectRepository $projectsRepo;

    private UserRepository $userRepo;

    private ClientRepository $clientsRepo;

    /**
     * Initializes dependencies.
     *
     * @throws BindingResolutionException
     */
    public function init(ProjectRepository $projectsRepo, UserRepository $userRepo, ClientRepository $clientsRepo): void
    {
        self::dispatch_event('api_key_init', $this);

        $this->projectsRepo = $projectsRepo;
        $this->userRepo = $userRepo;
        $this->clientsRepo = $clientsRepo;
    }

    /**
     * Displays the API key edit form.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            return $this->tpl->display('errors.error403');
        }

        $row = $this->userRepo->getUser($id);

        $values = [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'user' => $row['username'],
            'phone' => $row['phone'],
            'status' => $row['status'],
            'role' => $row['role'],
            'hours' => $row['hours'],
            'wage' => $row['wage'],
            'clientId' => $row['clientId'],
            'source' => $row['source'],
            'pwReset' => $row['pwReset'],
        ];

        $this->assignTemplateVars($id, $values);

        return $this->tpl->displayPartial('api.apiKey');
    }

    /**
     * Handles API key updates.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            return $this->tpl->display('errors.error403');
        }

        $row = $this->userRepo->getUser($id);

        $values = [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'user' => $row['username'],
            'phone' => $row['phone'],
            'status' => $row['status'],
            'role' => $row['role'],
            'hours' => $row['hours'],
            'wage' => $row['wage'],
            'clientId' => $row['clientId'],
            'source' => $row['source'],
            'pwReset' => $row['pwReset'],
        ];

        if (isset($_POST['save'])) {
            if (isset($_POST[session('formTokenName')]) && $_POST[session('formTokenName')] == session('formTokenValue')) {
                $values = [
                    'firstname' => ($_POST['firstname'] ?? $row['firstname']),
                    'lastname' => '',
                    'user' => $row['username'],
                    'phone' => '',
                    'status' => ($_POST['status'] ?? $row['status']),
                    'role' => ($_POST['role'] ?? $row['role']),
                    'hours' => '',
                    'wage' => '',
                    'clientId' => '',
                    'password' => '',
                    'source' => 'api',
                    'pwReset' => '',
                ];

                $this->userRepo->editUser($values, $id);

                if (isset($_POST['projects'])) {
                    if ($_POST['projects'][0] !== '0') {
                        $this->projectsRepo->editUserProjectRelations($id, $_POST['projects']);
                    } else {
                        $this->projectsRepo->deleteAllProjectRelations($id);
                    }
                } else {
                    $this->projectsRepo->deleteAllProjectRelations($id);
                }

                $this->tpl->setNotification($this->language->__('notifications.key_updated'), 'success', 'apikey_updated');
            } else {
                $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
            }
        }

        $this->assignTemplateVars($id, $values);

        return $this->tpl->displayPartial('api.apiKey');
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(int $id, array $values): void
    {
        $projects = $this->projectsRepo->getUserProjectRelation($id);

        $projectrelation = [];
        foreach ($projects as $projectId) {
            $projectrelation[] = $projectId['projectId'];
        }

        $this->tpl->assign('allProjects', $this->projectsRepo->getAll());
        $this->tpl->assign('roles', Roles::getRoles());
        $this->tpl->assign('clients', $this->clientsRepo->getAll());

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permitted_chars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permitted_chars), 0, 32)]);

        $this->tpl->assign('values', $values);
        $this->tpl->assign('relations', $projectrelation);
        $this->tpl->assign('status', $this->userRepo->status);
        $this->tpl->assign('id', $id);
    }
}
