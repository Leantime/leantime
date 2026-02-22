<?php

namespace Leantime\Domain\WorkStructure\Models;

/**
 * WorkStructure model — defines a structure type (e.g., "Project", "Logic Model").
 */
class WorkStructure
{
    public ?int $id = null;

    public string $title = '';

    public string $description = '';

    public string $type = 'custom';

    public ?int $createdBy = null;

    public ?string $meta = null;

    public ?string $createdAt = null;

    public ?string $modifiedAt = null;

    /** @var ElementDefinition[] */
    public array $elements = [];

    /** @var RelationshipDefinition[] */
    public array $relationships = [];
}
