<?php

namespace Leantime\Domain\Connector\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Connector\Models\Integration as IntegrationModel;
use Leantime\Domain\Connector\Repositories\LeantimeEntities;
use Leantime\Domain\Connector\Services\Connector;
use Leantime\Domain\Connector\Services\Integrations as IntegrationService;
use Leantime\Domain\Connector\Services\Providers;
use Symfony\Component\HttpFoundation\Response;

class Integration extends Controller
{
    private Providers $providerService;

    private IntegrationService $integrationService;

    private LeantimeEntities $leantimeEntities;

    private array $values = [];

    private array $fields = [];

    private Connector $connectorService;

    /**
     * Initializes dependencies.
     */
    public function init(
        Providers $providerService,
        IntegrationService $integrationService,
        LeantimeEntities $leantimeEntities,
        Connector $connectorService
    ): void {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $this->providerService = $providerService;
        $this->leantimeEntities = $leantimeEntities;
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
        return $this->handleIntegration();
    }

    /**
     * Handles integration wizard form submissions.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        return $this->handleIntegration();
    }

    /**
     * Handles the multi-step integration wizard.
     */
    private function handleIntegration(): Response
    {
        $params = $_REQUEST;

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

            if ($connection instanceof \Symfony\Component\HttpFoundation\Response) {
                return $connection;
            }
        }

        if ($params['step'] == 'entity') {
            $this->tpl->assign('providerEntities', $provider->getEntities());
            $this->tpl->assign('leantimeEntities', $this->leantimeEntities->availableLeantimeEntities);

            return $this->tpl->display('connector.integrationEntity');
        }

        if ($params['step'] == 'fields') {
            return $this->handleFieldsStep($params, $provider, $currentIntegration);
        }

        if ($params['step'] == 'sync') {
            return $this->tpl->display('connector.integrationSync');
        }

        if ($params['step'] == 'parse') {
            return $this->handleParseStep($provider);
        }

        if ($params['step'] == 'import') {
            return $this->handleImportStep();
        }

        return new Response;
    }

    /**
     * Handles the fields mapping step.
     */
    private function handleFieldsStep(array $params, object $provider, IntegrationModel $currentIntegration): Response
    {
        if (isset($_POST['leantimeEntities'])) {
            $entity = $_POST['leantimeEntities'];
            session(['currentImportEntity' => $entity]);
        } elseif (session()->exists('currentImportEntity') && session('currentImportEntity') != '') {
            $entity = session('currentImportEntity');
        } else {
            $this->tpl->setNotification('Entity not set', 'error');

            return Frontcontroller::redirect(BASE_URL.'/connector/integration?provider='.$provider->id.'');
        }

        $currentIntegration->entity = $entity;

        $flags = $this->connectorService->getEntityFlags($entity);

        $this->integrationService->patch($currentIntegration->id, ['entity' => $entity]);

        if (isset($currentIntegration->fields) && $currentIntegration->fields != '') {
            $this->tpl->assign('providerFields', explode(',', $currentIntegration->fields));
        } else {
            $this->tpl->assign('providerFields', $provider->getFields());
        }
        $this->tpl->assign('flags', $flags);
        $this->tpl->assign('leantimeFields', $this->leantimeEntities->availableLeantimeEntities[$entity]['fields']);

        return $this->tpl->display('connector.integrationFields');
    }

    /**
     * Handles the import review/parse step.
     */
    private function handleParseStep(object $provider): Response
    {
        $this->values = $provider->geValues();

        $this->fields = [];
        $this->fields = $this->connectorService->getFieldMappings($_POST);

        $flags = [];
        $flags = $this->connectorService->parseValues($this->fields, $this->values, session('currentImportEntity'));

        $this->tpl->assign('values', $this->values);
        $this->tpl->assign('fields', $this->fields);
        $this->tpl->assign('flags', $flags);

        return $this->tpl->display('connector.integrationImport');
    }

    /**
     * Handles the final import execution step.
     */
    private function handleImportStep(): Response
    {
        $values = safe_unserialize(session('serValues'), []);
        $fields = safe_unserialize(session('serFields'), []);

        $result = $this->connectorService->importValues($fields, $values, session('currentImportEntity'));

        if ($result !== true) {
            $this->tpl->setNotification('There was a problem with the import '.$result, 'error');
        }

        return $this->tpl->display('connector.integrationConfirm');
    }
}
