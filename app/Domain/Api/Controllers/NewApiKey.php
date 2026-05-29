<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Symfony\Component\HttpFoundation\Response;

class NewApiKey extends Controller
{
    private ApiService $APIService;

    /**
     * Initializes dependencies.
     *
     * @throws BindingResolutionException
     */
    public function init(ApiService $APIService): void
    {
        self::dispatch_event('api_key_init', $this);

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
        $this->tpl->assign('allProjects', $this->APIService->getAllProjects());
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

            $projectRelation = (isset($_POST['projects']) && is_array($_POST['projects'])) ? $_POST['projects'] : [];

            $apiKeyValues = $this->APIService->createApiKeyWithProjects($values, $_POST['projects'] ?? null);

            $this->tpl->setNotification('notifications.key_created', 'success', 'apikey_created');
            $this->tpl->assign('apiKeyValues', $apiKeyValues);
        }

        $this->tpl->assign('values', $values);
        $this->tpl->assign('allProjects', $this->APIService->getAllProjects());
        $this->tpl->assign('roles', Roles::getRoles());
        $this->tpl->assign('relations', $projectRelation);

        return $this->tpl->displayPartial('api.newAPIKey');
    }
}
