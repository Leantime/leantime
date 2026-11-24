<?php

namespace Leantime\Domain\Connector\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Connector\Models\ImportResult;
use Leantime\Domain\Connector\Models\Integration as IntegrationModel;
use Leantime\Domain\Connector\Permissions\ConnectorPermissions;
use Leantime\Domain\Connector\Services\Connector;
use Leantime\Domain\Connector\Services\Integrations as IntegrationService;
use Leantime\Domain\Connector\Services\Providers;
use Leantime\Domain\Connector\Services\ReviewTable;
use Leantime\Domain\Projects\Services\Projects;
use Symfony\Component\HttpFoundation\Response;

class Integration extends Controller
{
    private Providers $providerService;

    private IntegrationService $integrationService;

    private Connector $connectorService;

    private Projects $projectService;

    private ReviewTable $reviewTable;

    /** Entities whose rows go into a project, so the matching step offers a project for all rows. */
    private const PROJECT_ENTITIES = ['tickets', 'milestones'];

    /**
     * Initializes dependencies.
     */
    public function init(
        Providers $providerService,
        IntegrationService $integrationService,
        Connector $connectorService,
        Projects $projectService,
        ReviewTable $reviewTable
    ): void {
        $this->providerService = $providerService;
        $this->integrationService = $integrationService;
        $this->connectorService = $connectorService;
        $this->projectService = $projectService;
        $this->reviewTable = $reviewTable;
    }

    /**
     * Displays the integration wizard step.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
    public function get(array $params): Response
    {
        return $this->handleIntegration($params);
    }

    /**
     * Handles integration wizard form submissions.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
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
            if (is_object($currentIntegration)) {
                $this->tpl->assign('integrationId', $currentIntegration->id);
            }
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
            // Importing writes data, so it only runs from the review step's Confirm form (POST).
            if (! $this->incomingRequest->isMethod('POST')) {
                return Frontcontroller::redirect($provider->stepUrl('connect'));
            }

            return $this->handleImportStep($provider);
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
            $this->tpl->setNotification($this->language->__('notification.import_entity_not_set'), 'error');

            return Frontcontroller::redirect($provider->stepUrl('connect', $currentIntegration->id ? (int) $currentIntegration->id : null));
        }

        $providerFields = $this->integrationService->resolveProviderFields($currentIntegration, $provider);
        $this->tpl->assign('providerFields', $providerFields);
        $this->tpl->assign('examples', $this->firstValues($provider, $providerFields));
        $this->tpl->assign('entity', $entity);
        $this->tpl->assign('entityName', $this->integrationService->getAvailableEntities()[$entity]['name'] ?? $entity);
        $this->tpl->assign('guide', $this->connectorService->getEntityGuide($entity));
        $this->tpl->assign('leantimeFields', $this->integrationService->getEntityFields($entity));
        // Rows without a project of their own go into the project chosen here (nothing is pre-selected).
        $this->tpl->assign('importProjects', in_array($entity, self::PROJECT_ENTITIES, true) ? $this->projectsByName() : []);

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
        $projectId = is_numeric($params['importProjectId'] ?? null) ? (int) $params['importProjectId'] : null;
        $entity = (string) session('currentImportEntity');
        $flags = $this->connectorService->parseValues($fields, $values, $entity, $projectId);
        $flags = is_array($flags) ? $flags : [];
        $problems = $this->connectorService->reviewProblems($flags);
        $leantimeFields = $this->integrationService->getEntityFields($entity);

        $this->tpl->assign('entity', $entity);
        $this->tpl->assign('entityName', $this->integrationService->getAvailableEntities()[$entity]['name'] ?? $entity);
        $this->tpl->assign('flags', $flags);
        $this->tpl->assign('generalProblems', $problems['general']);
        $this->tpl->assign('review', $this->reviewTable->build($entity, $fields, $values, $this->connectorService->reviewedValues(), $problems, $leantimeFields));
        $this->tpl->assign('importToken', $this->integrationService->getCachedImportToken());

        return $this->tpl->display('connector.integrationImport');
    }

    /**
     * Handles the final import execution step and shows what was and wasn't imported.
     *
     * @param  object  $provider  Provider instance
     */
    private function handleImportStep(object $provider): Response
    {
        // Claimed once: a reload, a second Confirm or a parallel request gets nothing, so the
        // rows can't be imported twice. The review step only stores rows it didn't flag.
        $payload = $this->integrationService->claimCachedImportPayload((string) $this->incomingRequest->request->get('importToken', ''));

        // Nothing to import: the rows were already imported, the review flagged them, another
        // tab reviewed something else since, or the session expired. Start over instead.
        if (empty($payload['values']) || empty($payload['fields'])) {
            $this->tpl->setNotification($this->language->__('notification.import_nothing_to_import'), 'error');

            return Frontcontroller::redirect($provider->stepUrl('connect'));
        }

        // A large file can take longer than a small server's default limit; raise it where allowed.
        if (function_exists('set_time_limit')) {
            set_time_limit(300);
        }

        // The entity that was reviewed, not the one currently picked (another tab may have changed it).
        $result = $this->connectorService->importValues($payload['fields'], $payload['values'], $payload['entity']);

        if (! $result instanceof ImportResult) {
            $this->tpl->setNotification($this->language->__('notification.import_entity_not_set'), 'error');

            return Frontcontroller::redirect($provider->stepUrl('connect'));
        }

        $this->tpl->assign('importResult', $result);
        $this->tpl->assign('importedEntity', (string) $payload['entity']);
        $this->tpl->assign('importedProjects', $this->projectsOf($payload['values']));

        return $this->tpl->display('connector.integrationConfirm');
    }

    /**
     * The projects the imported rows went into, by id.
     *
     * @param  array<int, array<string, mixed>>  $rows  The imported rows (to-dos and milestones carry projectId).
     * @return array<int, string> Project id => name.
     */
    private function projectsOf(array $rows): array
    {
        $projects = [];
        foreach ($rows as $row) {
            $projectId = (int) ($row['projectId'] ?? 0);
            if ($projectId > 0 && ! isset($projects[$projectId])) {
                $project = $this->projectService->getProject($projectId);
                $projects[$projectId] = is_array($project) ? (string) $project['name'] : (string) $projectId;
            }
        }

        return $projects;
    }

    /**
     * The first filled value of each column, so the matching step shows what a column holds.
     *
     * @param  object  $provider  Provider instance.
     * @param  array<int, string>  $headings  The columns of the file.
     * @return array<string, string> Heading => first value ('' when the column is empty).
     */
    private function firstValues(object $provider, array $headings): array
    {
        $rows = method_exists($provider, 'geValues') ? (array) $provider->geValues() : [];
        $examples = array_fill_keys(array_map('strval', $headings), '');

        foreach ($rows as $row) {
            foreach ($examples as $heading => $example) {
                if ($example === '' && trim((string) ($row[$heading] ?? '')) !== '') {
                    $examples[$heading] = trim((string) $row[$heading]);
                }
            }
            if (! in_array('', $examples, true)) {
                break;
            }
        }

        return $examples;
    }

    /**
     * The projects the importer can choose for rows that name none, by name.
     *
     * @return array<int, array<string, mixed>>
     */
    private function projectsByName(): array
    {
        $projects = $this->projectService->getAllProjects();
        usort($projects, fn (array $a, array $b) => strnatcasecmp((string) $a['name'], (string) $b['name']));

        return $projects;
    }
}
