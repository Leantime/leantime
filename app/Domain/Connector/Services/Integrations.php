<?php

namespace Leantime\Domain\Connector\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Connector\Models\Integration as IntegrationModel;
use Leantime\Domain\Connector\Repositories\Integrations as IntegrationsRepo;
use Leantime\Domain\Connector\Repositories\LeantimeEntities;

class Integrations
{
    private IntegrationsRepo $integrationRepo;

    private LeantimeEntities $leantimeEntities;

    /**
     * Initializes the service dependencies.
     */
    public function __construct(IntegrationsRepo $integrationRepo, LeantimeEntities $leantimeEntities)
    {
        $this->integrationRepo = $integrationRepo;
        $this->leantimeEntities = $leantimeEntities;
    }

    /**
     * Gets a single integration by id.
     *
     * @api
     *
     * @param  int  $id  Integration id
     * @return object|array|false The integration record or false when not found
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function get(int $id): object|array|false
    {
        return $this->integrationRepo->get($id);
    }

    /**
     * Updates a ticket from an integration. Not yet implemented.
     *
     * @api
     */
    public function updateTicket(object|array $object): bool
    {
        // TODO: Implement update() method.
        return false;
    }

    /**
     * Creates a new integration record.
     *
     * @api
     *
     * @throws \ReflectionException
     */
    public function create(object|array $object): int|false
    {
        return $this->integrationRepo->insert($object);
    }

    /**
     * Deletes an integration. Not yet implemented.
     *
     * @api
     */
    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
        return false;
    }

    /**
     * Returns all integrations matching the given search params. Not yet implemented.
     *
     * @api
     */
    public function getAll(?array $searchparams = null): array|false
    {
        // TODO: Implement getAll() method.
        return false;
    }

    /**
     * Patches an integration record with the given values.
     *
     * @api
     *
     * @param  int  $id  Integration id
     * @param  array  $params  Column => value pairs to update
     */
    public function patch(int $id, array $params): bool
    {
        return $this->integrationRepo->patch($id, $params);
    }

    /**
     * Returns the list of Leantime entities available as import targets.
     *
     * @api
     *
     * @return array Map of entity key => entity definition
     */
    public function getAvailableEntities(): array
    {
        return $this->leantimeEntities->availableLeantimeEntities;
    }

    /**
     * Returns the available field definitions for a given Leantime entity.
     *
     * @api
     *
     * @param  string  $entity  Entity key (e.g. tickets, projects, users)
     * @return array Map of field key => field definition
     */
    public function getEntityFields(string $entity): array
    {
        return $this->leantimeEntities->availableLeantimeEntities[$entity]['fields'] ?? [];
    }

    /**
     * Resolves the import entity for the fields-mapping step.
     *
     * Encapsulates the request/session fallback chain: prefers the submitted
     * leantimeEntities value (persisting it to the session), otherwise falls
     * back to the previously stored session entity. When an entity is resolved
     * it hydrates the given integration model and patches the integration record.
     *
     * @api
     *
     * @param  array  $request  Request parameters (expects optional 'leantimeEntities')
     * @param  IntegrationModel  $currentIntegration  Integration model to hydrate with the resolved entity
     * @return string|null The resolved entity key, or null when no entity could be determined
     */
    public function resolveImportEntity(array $request, IntegrationModel $currentIntegration): ?string
    {
        if (isset($request['leantimeEntities'])) {
            $entity = $request['leantimeEntities'];
            session(['currentImportEntity' => $entity]);
        } elseif (session()->exists('currentImportEntity') && session('currentImportEntity') != '') {
            $entity = session('currentImportEntity');
        } else {
            return null;
        }

        $currentIntegration->entity = $entity;

        $this->patch($currentIntegration->id, ['entity' => $entity]);

        return $entity;
    }

    /**
     * Resolves the provider fields used in the fields-mapping step.
     *
     * Uses the persisted integration fields when present, otherwise falls back
     * to the live provider fields.
     *
     * @api
     *
     * @param  IntegrationModel  $currentIntegration  Integration model that may carry stored fields
     * @param  object  $provider  Provider instance exposing getFields()
     * @return array List of provider field identifiers
     */
    public function resolveProviderFields(IntegrationModel $currentIntegration, object $provider): array
    {
        if (isset($currentIntegration->fields) && $currentIntegration->fields != '') {
            return explode(',', $currentIntegration->fields);
        }

        return $provider->getFields();
    }

    /**
     * Reads the cached, serialized import payload (fields + values) from the session.
     *
     * Pairs with Connector::cacheSerializedFieldValues() which writes the keys.
     * Uses safe_unserialize() to avoid object injection.
     *
     * @api
     *
     * @return array{values: array, fields: array} The decoded values and field mappings
     */
    public function getCachedImportPayload(): array
    {
        return [
            'values' => safe_unserialize(session('serValues'), []),
            'fields' => safe_unserialize(session('serFields'), []),
        ];
    }
}
