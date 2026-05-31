<?php

namespace Leantime\Domain\Connector\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Connector\Models\Integration as IntegrationModel;
use Leantime\Domain\Connector\Services\Connector;
use Leantime\Domain\Connector\Services\Integrations as IntegrationService;
use Leantime\Domain\Connector\Services\Providers;
use Symfony\Component\HttpFoundation\Response;

class Integration extends Controller
{
    private Providers $providerService;

    private IntegrationService $integrationService;

    private Connector $connectorService;

    /**
     * Initializes dependencies.
     */
    public function init(
        Providers $providerService,
        IntegrationService $integrationService,
        Connector $connectorService
    ): void {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $this->providerService = $providerService;
        $this->integrationService = $integrationService;
        $this->connectorService = $connectorService;
    }

    /**
     * Displays the integration wizard step.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        return $this->handleIntegration($params);
    }

    /**
     * Handles integration wizard form submissions.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        return $this->handleIntegration($params);
    }

    /**
     * Routes the request to the correct integration wizard step.
     *
     * @param  array  $params  Request parameters
     */
    private function handleIntegration(array $params): Response
    {
        if (! session()->exists('currentImportEntity')) {
            session(['currentImportEntity' => '']);
        }

        if (! isset($params['provider'])) {
            return new Response;
        }

        $provider = $this->providerService->getProvider($params['provider']);
        $this->tpl->assign('provider', $provider);

        $currentIntegration = app()->make(IntegrationModel::class);

        if (isset($params['integrationId'])) {
            $currentIntegration = $this->integrationService->get($params['integrationId']);
            $this->tpl->assign('integrationId', $currentIntegration->id);
        }

        if (! isset($params['step'])) {
            return $this->tpl->display('connector.newIntegration');
        }

        if ($params['step'] == 'connect') {
            $connection = $provider->connect();

            if ($connection instanceof Response) {
                return $connection;
            }
        }

        if ($params['step'] == 'entity') {
            $this->tpl->assign('providerEntities', $provider->getEntities());
            $this->tpl->assign('leantimeEntities', $this->integrationService->getAvailableEntities());

            return $this->tpl->display('connector.integrationEntity');
        }

        if ($params['step'] == 'fields') {
            return $this->handleFieldsStep($this->incomingRequest->request->all(), $provider, $currentIntegration);
        }

        if ($params['step'] == 'sync') {
            return $this->tpl->display('connector.integrationSync');
        }

        if ($params['step'] == 'parse') {
            return $this->handleParseStep($this->incomingRequest->request->all(), $provider);
        }

        if ($params['step'] == 'import') {
            return $this->handleImportStep();
        }

        return new Response;
    }

    /**
     * Handles the fields mapping step.
     *
     * @param  array  $params  POST request parameters
     * @param  object  $provider  Provider instance
     * @param  IntegrationModel  $currentIntegration  Integration being configured
     */
    private function handleFieldsStep(array $params, object $provider, IntegrationModel $currentIntegration): Response
    {
        $entity = $this->integrationService->resolveImportEntity($params, $currentIntegration);

        if ($entity === null) {
            $this->tpl->setNotification('Entity not set', 'error');

            return Frontcontroller::redirect(BASE_URL.'/connector/integration?provider='.$provider->id.'');
        }

        $this->tpl->assign('providerFields', $this->integrationService->resolveProviderFields($currentIntegration, $provider));
        $this->tpl->assign('flags', $this->connectorService->getEntityFlags($entity));
        $this->tpl->assign('leantimeFields', $this->integrationService->getEntityFields($entity));

        return $this->tpl->display('connector.integrationFields');
    }

    /**
     * Handles the import review/parse step.
     *
     * @param  array  $params  POST request parameters
     * @param  object  $provider  Provider instance
     */
    private function handleParseStep(array $params, object $provider): Response
    {
        $values = $provider->geValues();
        $fields = $this->connectorService->getFieldMappings($params);
        $flags = $this->connectorService->parseValues($fields, $values, session('currentImportEntity'));

        $this->tpl->assign('values', $values);
        $this->tpl->assign('fields', $fields);
        $this->tpl->assign('flags', $flags);

        return $this->tpl->display('connector.integrationImport');
    }

    /**
     * Handles the final import execution step.
     */
    private function handleImportStep(): Response
    {
        $payload = $this->integrationService->getCachedImportPayload();

        $result = $this->connectorService->importValues($payload['fields'], $payload['values'], session('currentImportEntity'));

        if ($result !== true) {
            $this->tpl->setNotification('There was a problem with the import '.$result, 'error');
        }

        return $this->tpl->display('connector.integrationConfirm');
    }
}
