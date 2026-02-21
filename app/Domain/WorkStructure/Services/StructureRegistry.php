<?php

namespace Leantime\Domain\WorkStructure\Services;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\WorkStructure\Events\ElementTypeRegistered;
use Leantime\Domain\WorkStructure\Events\MappingCreated;
use Leantime\Domain\WorkStructure\Events\StructureRegistered;
use Leantime\Domain\WorkStructure\Models\WorkStructure;
use Leantime\Domain\WorkStructure\Repositories\WorkStructureRepository;

/**
 * Plugin registration service for work structures.
 *
 * Provides an idempotent API for plugins to register their structure
 * definitions, elements, relationships, and cross-structure mappings.
 *
 * @api
 */
class StructureRegistry
{
    use DispatchesEvents;

    private const CACHE_PREFIX = 'workstructure.';

    /**
     * @param  WorkStructureRepository  $repo  WorkStructure repository
     */
    public function __construct(
        private WorkStructureRepository $repo
    ) {}

    /**
     * Register a structure with elements and relationships (idempotent).
     *
     * @param  string  $title  Structure title (unique)
     * @param  string  $type  'system', 'plugin', 'custom'
     * @param  array  $elements  Array of element definitions [{typeKey, label, description?, domainReference?, sortOrder, meta?}]
     * @param  array  $relationships  Array of relationship defs [{fromTypeKey, toTypeKey, relationshipType, description?}]
     * @return int Structure ID
     *
     * @api
     */
    public function register(string $title, string $type, array $elements, array $relationships = []): int
    {
        if ($this->has($title)) {
            $structure = $this->get($title);

            return $structure->id;
        }

        $structureId = $this->repo->createStructure([
            'title' => $title,
            'type' => $type,
        ]);

        // Add elements
        $elementIds = [];
        foreach ($elements as $element) {
            $elementId = $this->repo->addElement([
                'structureId' => $structureId,
                'typeKey' => $element['typeKey'],
                'label' => $element['label'],
                'description' => $element['description'] ?? '',
                'domainReference' => $element['domainReference'] ?? null,
                'sortOrder' => $element['sortOrder'] ?? 0,
                'meta' => $element['meta'] ?? null,
            ]);
            $elementIds[$element['typeKey']] = $elementId;

            ElementTypeRegistered::dispatch($structureId, $element['typeKey'], $element['label']);
        }

        // Add relationships (resolve typeKey → element ID)
        foreach ($relationships as $rel) {
            $fromId = $elementIds[$rel['fromTypeKey']] ?? null;
            $toId = $elementIds[$rel['toTypeKey']] ?? null;

            if ($fromId !== null && $toId !== null) {
                $this->repo->addRelationship([
                    'structureId' => $structureId,
                    'fromElementId' => $fromId,
                    'toElementId' => $toId,
                    'relationshipType' => $rel['relationshipType'],
                    'description' => $rel['description'] ?? '',
                ]);
            }
        }

        $this->clearCache($title);
        StructureRegistered::dispatch($structureId, $title, $type);

        return $structureId;
    }

    /**
     * Register cross-structure mappings (idempotent per source element + target structure).
     *
     * @param  int  $sourceStructureId  Source structure ID
     * @param  int  $targetStructureId  Target structure ID
     * @param  array  $mappings  Array of [{sourceTypeKey, targetTypeKey, mappingType}]
     *
     * @api
     */
    public function registerMappings(int $sourceStructureId, int $targetStructureId, array $mappings): void
    {
        foreach ($mappings as $mapping) {
            $sourceElement = $this->repo->getElementByTypeKey($sourceStructureId, $mapping['sourceTypeKey']);
            $targetElement = $this->repo->getElementByTypeKey($targetStructureId, $mapping['targetTypeKey']);

            if ($sourceElement === null || $targetElement === null) {
                continue;
            }

            if ($this->repo->mappingExists($sourceStructureId, $sourceElement->id, $targetStructureId)) {
                continue;
            }

            $this->repo->addMapping([
                'sourceStructureId' => $sourceStructureId,
                'sourceElementId' => $sourceElement->id,
                'targetStructureId' => $targetStructureId,
                'targetElementId' => $targetElement->id,
                'mappingType' => $mapping['mappingType'] ?? 'generates',
            ]);

            MappingCreated::dispatch($sourceStructureId, $targetStructureId, $mapping['mappingType'] ?? 'generates');
        }
    }

    /**
     * Check if a structure is registered.
     *
     * @param  string  $title  Structure title
     *
     * @api
     */
    public function has(string $title): bool
    {
        return Cache::rememberForever(self::CACHE_PREFIX.'exists.'.$title, function () use ($title) {
            return $this->repo->structureExists($title);
        });
    }

    /**
     * Get a registered structure by title (cached).
     *
     * @param  string  $title  Structure title
     *
     * @api
     */
    public function get(string $title): ?WorkStructure
    {
        return Cache::rememberForever(self::CACHE_PREFIX.'structure.'.$title, function () use ($title) {
            $structure = $this->repo->getStructureByTitle($title);

            if ($structure !== null && $structure->id !== null) {
                $structure->elements = $this->repo->getElements($structure->id);
                $structure->relationships = $this->repo->getRelationships($structure->id);
            }

            return $structure;
        });
    }

    /**
     * Clear cache for a structure.
     *
     * @param  string  $title  Structure title
     */
    private function clearCache(string $title): void
    {
        Cache::forget(self::CACHE_PREFIX.'exists.'.$title);
        Cache::forget(self::CACHE_PREFIX.'structure.'.$title);
    }
}
