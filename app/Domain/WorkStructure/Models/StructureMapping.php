<?php

namespace Leantime\Domain\WorkStructure\Models;

/**
 * StructureMapping model — defines cross-structure element mappings
 * (e.g., Logic Model "output" → Project "milestone").
 */
class StructureMapping
{
    public ?int $id = null;

    public int $sourceStructureId = 0;

    public int $sourceElementId = 0;

    public int $targetStructureId = 0;

    public int $targetElementId = 0;

    public string $mappingType = 'generates';

    public ?string $meta = null;
}
