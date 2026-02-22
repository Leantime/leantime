<?php

namespace Leantime\Domain\WorkStructure\Models;

/**
 * RelationshipDefinition model — defines intra-structure relationships
 * (e.g., "task belongs_to milestone").
 */
class RelationshipDefinition
{
    public ?int $id = null;

    public int $structureId = 0;

    public int $fromElementId = 0;

    public int $toElementId = 0;

    public string $relationshipType = '';

    public string $description = '';

    public ?string $meta = null;
}
