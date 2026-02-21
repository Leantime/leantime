<?php

namespace Leantime\Domain\WorkStructure\Models;

/**
 * ElementDefinition model — defines an element type within a structure
 * (e.g., "milestone", "task", "goal").
 */
class ElementDefinition
{
    public ?int $id = null;

    public int $structureId = 0;

    public string $typeKey = '';

    public string $label = '';

    public string $description = '';

    public ?string $domainReference = null;

    public int $sortOrder = 0;

    public ?string $meta = null;

    public ?string $createdAt = null;
}
