<?php

namespace Leantime\Domain\WorkStructure\Services;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\WorkStructure\Models\ElementDefinition;
use Leantime\Domain\WorkStructure\Models\RelationshipDefinition;
use Leantime\Domain\WorkStructure\Models\WorkStructure;
use Leantime\Domain\WorkStructure\Repositories\WorkStructureRepository;

/**
 * CRUD service for work structures, elements, and relationships.
 *
 * @api
 */
class WorkStructureService
{
    use DispatchesEvents;

    /**
     * @param  WorkStructureRepository  $repo  WorkStructure repository
     */
    public function __construct(
        private WorkStructureRepository $repo
    ) {}

    /**
     * Get a structure by ID with its elements and relationships.
     *
     * @param  int  $id  Structure ID
     *
     * @api
     */
    public function getStructure(int $id): ?WorkStructure
    {
        $structure = $this->repo->getStructure($id);

        if ($structure !== null) {
            $structure->elements = $this->repo->getElements($id);
            $structure->relationships = $this->repo->getRelationships($id);
        }

        return $structure;
    }

    /**
     * Get a structure by title with its elements and relationships.
     *
     * @param  string  $title  Structure title
     *
     * @api
     */
    public function getStructureByTitle(string $title): ?WorkStructure
    {
        $structure = $this->repo->getStructureByTitle($title);

        if ($structure !== null && $structure->id !== null) {
            $structure->elements = $this->repo->getElements($structure->id);
            $structure->relationships = $this->repo->getRelationships($structure->id);
        }

        return $structure;
    }

    /**
     * Get all elements for a structure, ordered by sort_order.
     *
     * @param  int  $structureId  Structure ID
     * @return ElementDefinition[]
     *
     * @api
     */
    public function getElements(int $structureId): array
    {
        return $this->repo->getElements($structureId);
    }

    /**
     * Get all intra-structure relationships.
     *
     * @param  int  $structureId  Structure ID
     * @return RelationshipDefinition[]
     *
     * @api
     */
    public function getRelationships(int $structureId): array
    {
        return $this->repo->getRelationships($structureId);
    }

    /**
     * Create a new structure.
     *
     * @param  array  $values  Structure data (title, description, type, createdBy, meta)
     * @return int Created structure ID
     *
     * @api
     */
    public function createStructure(array $values): int
    {
        return $this->repo->createStructure($values);
    }

    /**
     * Add an element to a structure.
     *
     * @param  array  $values  Element data (structureId, typeKey, label, description, domainReference, sortOrder, meta)
     * @return int Created element ID
     *
     * @api
     */
    public function addElement(array $values): int
    {
        return $this->repo->addElement($values);
    }

    /**
     * Add an intra-structure relationship.
     *
     * @param  array  $values  Relationship data (structureId, fromElementId, toElementId, relationshipType, description, meta)
     * @return int Created relationship ID
     *
     * @api
     */
    public function addRelationship(array $values): int
    {
        return $this->repo->addRelationship($values);
    }
}
