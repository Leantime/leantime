<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Users\Services\Users;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class EditUser extends Controller
{
    private ProjectService $projectService;

    private ClientService $clientService;

    private Users $userService;

    /**
     * Initializes dependencies.
     */
    public function init(
        ProjectService $projectService,
        ClientService $clientService,
        Users $userService
    ): void {
        $this->projectService = $projectService;
        $this->clientService = $clientService;
        $this->userService = $userService;
    }

    /**
     * Displays the edit user form.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];
        $row = $this->userService->getUser($id);

        if ($row === false) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        if (array_key_exists('resendInvite', $_GET)) {
            return $this->handleResendInvite($id, $row);
        }

        $values = $this->buildValuesFromUser($row);
        $projectrelation = $this->getUserProjectIds($id);

        $this->generateFormTokens();

        $this->tpl->assign('values', $values);
        $this->tpl->assign('relations', $projectrelation);
        $this->tpl->assign('id', $id);
        $this->assignTemplateVars();

        return $this->tpl->display('users.editUser');
    }

    /**
     * Handles user profile updates.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];
        $row = $this->userService->getUser($id);

        if ($row === false) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        $values = $this->buildValuesFromUser($row);
        $edit = false;

        if (isset($_POST['save'])) {
            if (! isset($_POST[session('formTokenName')]) || $_POST[session('formTokenName')] != session('formTokenValue')) {
                $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
            } else {
                $values = $this->buildValuesFromPost($row);
                $edit = $this->validateUserUpdate($values, $row, $id);
            }
        }

        if ($edit) {
            $this->userService->editUser($values, $id);
            $this->updateProjectRelations($id);
            $this->tpl->setNotification($this->language->__('notifications.user_edited'), 'success');
        }

        $projectrelation = $this->getUserProjectIds($id);

        $this->generateFormTokens();

        $this->tpl->assign('values', $values);
        $this->tpl->assign('relations', $projectrelation);
        $this->tpl->assign('id', $id);
        $this->assignTemplateVars();

        return $this->tpl->display('users.editUser');
    }

    /**
     * Handles the resend invite action.
     */
    private function handleResendInvite(int $id, array $row): Response
    {
        $values = $this->buildValuesFromUser($row);

        if (session()->exists('lastInvite.'.$id) && session('lastInvite.'.$id) >= time() - 240) {
            $this->tpl->setNotification($this->language->__('notification.invite_too_soon'), 'error');

            return Frontcontroller::redirect(BASE_URL.'/users/editUser/'.$id);
        }

        session(['lastInvite.'.$id => time()]);

        if (empty($values['pwReset'])) {
            $inviteCode = Uuid::uuid4()->toString();
            $this->userService->patchUser($id, ['pwReset' => $inviteCode]);
            $values['pwReset'] = $inviteCode;
        }

        $this->userService->sendUserInvite(
            inviteCode: $values['pwReset'],
            user: $values['user']
        );

        $this->tpl->setNotification($this->language->__('notification.invitation_sent'), 'success', 'userinvitation_sent');

        return Frontcontroller::redirect(BASE_URL.'/users/editUser/'.$id);
    }

    /**
     * Builds a values array from the user database row.
     */
    private function buildValuesFromUser(array $row): array
    {
        return [
            'id' => $row['id'],
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
            'jobTitle' => $row['jobTitle'],
            'jobLevel' => $row['jobLevel'],
            'department' => $row['department'],
        ];
    }

    /**
     * Builds a values array from POST data, falling back to the original user row.
     */
    private function buildValuesFromPost(array $row): array
    {
        return [
            'id' => $row['id'],
            'firstname' => $_POST['firstname'] ?? $row['firstname'],
            'lastname' => $_POST['lastname'] ?? $row['lastname'],
            'user' => $_POST['user'] ?? $row['username'],
            'phone' => $_POST['phone'] ?? $row['phone'],
            'status' => $_POST['status'] ?? $row['status'],
            'role' => $_POST['role'] ?? $row['role'],
            'hours' => $_POST['hours'] ?? $row['hours'],
            'wage' => $_POST['wage'] ?? $row['wage'],
            'clientId' => $_POST['client'] ?? $row['clientId'],
            'source' => $row['source'],
            'pwReset' => $row['pwReset'],
            'jobTitle' => $_POST['jobTitle'] ?? $row['jobTitle'],
            'jobLevel' => $_POST['jobLevel'] ?? $row['jobLevel'],
            'department' => $_POST['department'] ?? $row['department'],
        ];
    }

    /**
     * Validates user update data. Returns true if the update should proceed.
     */
    private function validateUserUpdate(array $values, array $row, int $id): bool
    {
        if ($values['user'] === '') {
            $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), 'error');

            return false;
        }

        if (isset($_POST['password']) && $_POST['password'] != $_POST['password2']) {
            $this->tpl->setNotification($this->language->__('notification.enter_email'), 'error');

            return false;
        }

        if (! filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
            $this->tpl->setNotification($this->language->__('notification.no_valid_email'), 'error');

            return false;
        }

        if ($row['username'] != $values['user'] && $this->userService->usernameExist($row['username'], $id)) {
            $this->tpl->setNotification($this->language->__('notification.user_exists'), 'error');

            return false;
        }

        return true;
    }

    /**
     * Updates project relations for a user based on POST data.
     */
    private function updateProjectRelations(int $userId): void
    {
        if (isset($_POST['projects'])) {
            if ($_POST['projects'][0] !== '0') {
                $this->projectService->editUserProjectRelations($userId, $_POST['projects']);
            } else {
                $this->projectService->deleteAllUserProjectRelations($userId);
            }
        } else {
            $this->projectService->deleteAllUserProjectRelations($userId);
        }
    }

    /**
     * Gets project IDs assigned to a user.
     *
     * @return int[]
     */
    private function getUserProjectIds(int $userId): array
    {
        $projects = $this->projectService->getUserProjectRelation($userId);
        $projectrelation = [];

        foreach ($projects as $projectId) {
            $projectrelation[] = $projectId['projectId'];
        }

        return $projectrelation;
    }

    /**
     * Generates CSRF form tokens.
     */
    private function generateFormTokens(): void
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permitted_chars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permitted_chars), 0, 32)]);
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(): void
    {
        $this->tpl->assign('allProjects', $this->projectService->getAll(true));
        $this->tpl->assign('roles', Roles::getRoles());
        $this->tpl->assign('clients', $this->clientService->getAll());
        $this->tpl->assign('status', $this->userService->getUserStatuses());
    }
}
