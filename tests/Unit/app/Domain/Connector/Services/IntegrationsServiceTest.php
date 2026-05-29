<?php

namespace Unit\app\Domain\Connector\Services;

use Leantime\Domain\Connector\Models\Integration as IntegrationModel;
use Leantime\Domain\Connector\Repositories\Integrations as IntegrationsRepo;
use Leantime\Domain\Connector\Repositories\LeantimeEntities;
use Leantime\Domain\Connector\Services\Integrations;
use Unit\TestCase;

/**
 * Unit tests for the integration-wizard orchestration extracted from the
 * Connector\Integration controller into the Integrations service.
 */
class IntegrationsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds the service with a real (DB-free) LeantimeEntities repo and a
     * stubbed integration repository.
     */
    private function makeService(array $repoOverrides = []): Integrations
    {
        return new Integrations(
            $this->make(IntegrationsRepo::class, $repoOverrides),
            new LeantimeEntities,
        );
    }

    public function test_get_entity_fields_returns_field_map_for_known_entity(): void
    {
        $fields = $this->makeService()->getEntityFields('tickets');

        $this->assertArrayHasKey('headline', $fields);
        $this->assertSame('Title', $fields['headline']['name']);
    }

    public function test_get_entity_fields_returns_empty_array_for_unknown_entity(): void
    {
        $this->assertSame([], $this->makeService()->getEntityFields('does-not-exist'));
    }

    public function test_get_available_entities_includes_core_entities(): void
    {
        $entities = $this->makeService()->getAvailableEntities();

        $this->assertArrayHasKey('tickets', $entities);
        $this->assertArrayHasKey('projects', $entities);
        $this->assertArrayHasKey('users', $entities);
    }

    public function test_resolve_provider_fields_uses_stored_fields_when_present(): void
    {
        $integration = new IntegrationModel;
        $integration->fields = 'colA,colB,colC';

        $provider = new class
        {
            public function getFields(): array
            {
                return ['providerOnly'];
            }
        };

        $result = $this->makeService()->resolveProviderFields($integration, $provider);

        $this->assertSame(['colA', 'colB', 'colC'], $result);
    }

    public function test_resolve_provider_fields_falls_back_to_provider(): void
    {
        $integration = new IntegrationModel;
        $integration->fields = '';

        $provider = new class
        {
            public function getFields(): array
            {
                return ['fieldFromProvider'];
            }
        };

        $result = $this->makeService()->resolveProviderFields($integration, $provider);

        $this->assertSame(['fieldFromProvider'], $result);
    }

    public function test_resolve_import_entity_uses_request_value_and_persists(): void
    {
        session()->forget('currentImportEntity');

        $patchCalls = [];
        $service = $this->makeService([
            'patch' => function ($id, $params) use (&$patchCalls) {
                $patchCalls[] = [$id, $params];

                return true;
            },
        ]);

        $integration = new IntegrationModel;
        $integration->id = 42;

        $entity = $service->resolveImportEntity(['leantimeEntities' => 'tickets'], $integration);

        $this->assertSame('tickets', $entity);
        $this->assertSame('tickets', $integration->entity);
        $this->assertSame('tickets', session('currentImportEntity'));
        $this->assertSame([[42, ['entity' => 'tickets']]], $patchCalls);
    }

    public function test_resolve_import_entity_falls_back_to_session(): void
    {
        session(['currentImportEntity' => 'projects']);

        $patchCalls = [];
        $service = $this->makeService([
            'patch' => function ($id, $params) use (&$patchCalls) {
                $patchCalls[] = [$id, $params];

                return true;
            },
        ]);

        $integration = new IntegrationModel;
        $integration->id = 7;

        $entity = $service->resolveImportEntity([], $integration);

        $this->assertSame('projects', $entity);
        $this->assertSame('projects', $integration->entity);
        $this->assertSame([[7, ['entity' => 'projects']]], $patchCalls);
    }

    public function test_resolve_import_entity_returns_null_when_unresolvable(): void
    {
        session(['currentImportEntity' => '']);

        $patched = false;
        $service = $this->makeService([
            'patch' => function () use (&$patched) {
                $patched = true;

                return true;
            },
        ]);

        $integration = new IntegrationModel;
        $integration->id = 1;

        $entity = $service->resolveImportEntity([], $integration);

        $this->assertNull($entity);
        $this->assertFalse($patched, 'No record should be patched when the entity cannot be resolved');
    }

    public function test_get_cached_import_payload_decodes_session_serialized_data(): void
    {
        $fields = [['sourceField' => 'a', 'leantimeField' => 'headline']];
        $values = [['a' => 'Hello']];

        session(['serFields' => serialize($fields)]);
        session(['serValues' => serialize($values)]);

        $payload = $this->makeService()->getCachedImportPayload();

        $this->assertSame($fields, $payload['fields']);
        $this->assertSame($values, $payload['values']);
    }

    public function test_get_cached_import_payload_defaults_to_empty_arrays(): void
    {
        session()->forget('serFields');
        session()->forget('serValues');

        $payload = $this->makeService()->getCachedImportPayload();

        $this->assertSame([], $payload['fields']);
        $this->assertSame([], $payload['values']);
    }
}
