<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Symfony\Component\HttpFoundation\Response;

/**
 * API-key controller.
 */
class ApiKey extends Controller
{
    private ApiService $apiService;

    private ClientService $clientService;

    /**
     * Initializes dependencies.
     *
     * @throws BindingResolutionException
     */
    public function init(ApiService $apiService, ClientService $clientService): void
    {
        self::dispatch_event('api_key_init', $this);

        $this->apiService = $apiService;
        $this->clientService = $clientService;
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

        $values = $this->apiService->getApiKeyFormValues($id);

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

        $values = $this->apiService->getApiKeyFormValues($id);

        if (isset($_POST['save'])) {
            if (isset($_POST[session('formTokenName')]) && $_POST[session('formTokenName')] == session('formTokenValue')) {
                $this->apiService->updateApiKey($id, $_POST, $_POST['projects'] ?? null);

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
     *
     * @throws \Exception
     */
    private function assignTemplateVars(int $id, array $values): void
    {
        $this->apiService->generateFormToken();

        $this->tpl->assign('allProjects', $this->apiService->getAllProjects());
        $this->tpl->assign('roles', Roles::getRoles());
        $this->tpl->assign('clients', $this->clientService->getAll());
        $this->tpl->assign('values', $values);
        $this->tpl->assign('relations', $this->apiService->getProjectRelationIds($id));
        $this->tpl->assign('status', $this->apiService->getUserStatusOptions());
        $this->tpl->assign('id', $id);
    }
}
