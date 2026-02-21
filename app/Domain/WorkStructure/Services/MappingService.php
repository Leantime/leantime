<?php

namespace Leantime\Domain\WorkStructure\Services;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\WorkStructure\Models\StructureMapping;
use Leantime\Domain\WorkStructure\Repositories\WorkStructureRepository;

/**
 * Cross-structure mapping query service.
 *
 * Resolves element type mappings between different work structures
 * (e.g., Logic Model "output" → Project "milestone").
 *
 * @api
 */
class MappingService
{
    use DispatchesEvents;

    /**
     * @param  WorkStructureRepository  $repo  WorkStructure repository
     */
    public function __construct(
        private WorkStructureRepository $repo
    ) {}

    /**
     * Get all mappings between two structures.
     *
     * @param  int  $sourceStructureId  Source structure ID
     * @param  int  $targetStructureId  Target structure ID
     * @return StructureMapping[]
     *
     * @api
     */
    public function getMappings(int $sourceStructureId, int $targetStructureId): array
    {
        return $this->repo->getMappings($sourceStructureId, $targetStructureId);
    }

    /**
     * Get the target element type key for a given source element type key.
     *
     * @param  int  $sourceStructureId  Source structure ID
     * @param  string  $sourceTypeKey  Source element type key
     * @param  int  $targetStructureId  Target structure ID
     * @return string|null Target element type key, or null if no mapping exists
     *
     * @api
     */
    public function getTargetElementKey(int $sourceStructureId, string $sourceTypeKey, int $targetStructureId): ?string
    {
        $sourceElement = $this->repo->getElementByTypeKey($sourceStructureId, $sourceTypeKey);

        if ($sourceElement === null) {
            return null;
        }

        $mappings = $this->repo->getMappings($sourceStructureId, $targetStructureId);

        foreach ($mappings as $mapping) {
            if ($mapping->sourceElementId === $sourceElement->id) {
                $targetElement = $this->repo->getElements($targetStructureId);

                foreach ($targetElement as $el) {
                    if ($el->id === $mapping->targetElementId) {
                        return $el->typeKey;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get the source element type key for a given target element type key.
     *
     * @param  int  $targetStructureId  Target structure ID
     * @param  string  $targetTypeKey  Target element type key
     * @param  int  $sourceStructureId  Source structure ID
     * @return string|null Source element type key, or null if no mapping exists
     *
     * @api
     */
    public function getSourceElementKey(int $targetStructureId, string $targetTypeKey, int $sourceStructureId): ?string
    {
        $targetElement = $this->repo->getElementByTypeKey($targetStructureId, $targetTypeKey);

        if ($targetElement === null) {
            return null;
        }

        $mappings = $this->repo->getMappings($sourceStructureId, $targetStructureId);

        foreach ($mappings as $mapping) {
            if ($mapping->targetElementId === $targetElement->id) {
                $sourceElements = $this->repo->getElements($sourceStructureId);

                foreach ($sourceElements as $el) {
                    if ($el->id === $mapping->sourceElementId) {
                        return $el->typeKey;
                    }
                }
            }
        }

        return null;
    }
}
