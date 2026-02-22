<?php

namespace Leantime\Domain\WorkStructure\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\WorkStructure\Models\ElementDefinition;
use Leantime\Domain\WorkStructure\Models\RelationshipDefinition;
use Leantime\Domain\WorkStructure\Models\StructureMapping;
use Leantime\Domain\WorkStructure\Models\WorkStructure;

/**
 * Repository for WorkStructure tables — CRUD via Laravel Query Builder.
 *
 * @api
 */
class WorkStructureRepository
{
    use DispatchesEvents;

    private ConnectionInterface $db;

    /**
     * @param  DbCore  $db  Database connection wrapper
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    // ─── Structures ──────────────────────────────────────────────────

    /**
     * Get a structure by ID.
     *
     * @param  int  $id  Structure ID
     */
    public function getStructure(int $id): ?WorkStructure
    {
        $row = $this->db->table('zp_work_structures')
            ->where('id', $id)
            ->first();

        return $row ? $this->hydrateStructure($row) : null;
    }

    /**
     * Get a structure by title.
     *
     * @param  string  $title  Structure title
     */
    public function getStructureByTitle(string $title): ?WorkStructure
    {
        $row = $this->db->table('zp_work_structures')
            ->where('title', $title)
            ->first();

        return $row ? $this->hydrateStructure($row) : null;
    }

    /**
     * Get all structures, optionally filtered by type.
     *
     * @param  string|null  $type  Filter by type ('system', 'plugin', 'custom')
     * @return WorkStructure[]
     */
    public function getAllStructures(?string $type = null): array
    {
        $query = $this->db->table('zp_work_structures');

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->get()
            ->map(fn ($row) => $this->hydrateStructure($row))
            ->all();
    }

    /**
     * Create a new structure.
     *
     * @param  array  $values  Structure data
     * @return int Inserted ID
     */
    public function createStructure(array $values): int
    {
        $now = now()->toDateTimeString();

        return (int) $this->db->table('zp_work_structures')->insertGetId([
            'title' => $values['title'],
            'description' => $values['description'] ?? '',
            'type' => $values['type'] ?? 'custom',
            'created_by' => $values['createdBy'] ?? null,
            'meta' => isset($values['meta']) ? json_encode($values['meta']) : null,
            'created_at' => $now,
            'modified_at' => $now,
        ]);
    }

    /**
     * Check if a structure exists by title.
     *
     * @param  string  $title  Structure title
     */
    public function structureExists(string $title): bool
    {
        return $this->db->table('zp_work_structures')
            ->where('title', $title)
            ->exists();
    }

    // ─── Elements ────────────────────────────────────────────────────

    /**
     * Get all elements for a structure.
     *
     * @param  int  $structureId  Structure ID
     * @return ElementDefinition[]
     */
    public function getElements(int $structureId): array
    {
        return $this->db->table('zp_work_structure_elements')
            ->where('structure_id', $structureId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($row) => $this->hydrateElement($row))
            ->all();
    }

    /**
     * Get an element by structure ID and type key.
     *
     * @param  int  $structureId  Structure ID
     * @param  string  $typeKey  Element type key
     */
    public function getElementByTypeKey(int $structureId, string $typeKey): ?ElementDefinition
    {
        $row = $this->db->table('zp_work_structure_elements')
            ->where('structure_id', $structureId)
            ->where('type_key', $typeKey)
            ->first();

        return $row ? $this->hydrateElement($row) : null;
    }

    /**
     * Add an element to a structure.
     *
     * @param  array  $values  Element data
     * @return int Inserted ID
     */
    public function addElement(array $values): int
    {
        return (int) $this->db->table('zp_work_structure_elements')->insertGetId([
            'structure_id' => $values['structureId'],
            'type_key' => $values['typeKey'],
            'label' => $values['label'],
            'description' => $values['description'] ?? '',
            'domain_reference' => $values['domainReference'] ?? null,
            'sort_order' => $values['sortOrder'] ?? 0,
            'meta' => isset($values['meta']) ? json_encode($values['meta']) : null,
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    // ─── Relationships ───────────────────────────────────────────────

    /**
     * Get all relationships for a structure.
     *
     * @param  int  $structureId  Structure ID
     * @return RelationshipDefinition[]
     */
    public function getRelationships(int $structureId): array
    {
        return $this->db->table('zp_work_structure_relationships')
            ->where('structure_id', $structureId)
            ->get()
            ->map(fn ($row) => $this->hydrateRelationship($row))
            ->all();
    }

    /**
     * Add an intra-structure relationship.
     *
     * @param  array  $values  Relationship data
     * @return int Inserted ID
     */
    public function addRelationship(array $values): int
    {
        return (int) $this->db->table('zp_work_structure_relationships')->insertGetId([
            'structure_id' => $values['structureId'],
            'from_element_id' => $values['fromElementId'],
            'to_element_id' => $values['toElementId'],
            'relationship_type' => $values['relationshipType'],
            'description' => $values['description'] ?? '',
            'meta' => isset($values['meta']) ? json_encode($values['meta']) : null,
        ]);
    }

    // ─── Mappings ────────────────────────────────────────────────────

    /**
     * Get all mappings between two structures.
     *
     * @param  int  $sourceStructureId  Source structure ID
     * @param  int  $targetStructureId  Target structure ID
     * @return StructureMapping[]
     */
    public function getMappings(int $sourceStructureId, int $targetStructureId): array
    {
        return $this->db->table('zp_work_structure_mappings')
            ->where('source_structure_id', $sourceStructureId)
            ->where('target_structure_id', $targetStructureId)
            ->get()
            ->map(fn ($row) => $this->hydrateMapping($row))
            ->all();
    }

    /**
     * Add a cross-structure mapping.
     *
     * @param  array  $values  Mapping data
     * @return int Inserted ID
     */
    public function addMapping(array $values): int
    {
        return (int) $this->db->table('zp_work_structure_mappings')->insertGetId([
            'source_structure_id' => $values['sourceStructureId'],
            'source_element_id' => $values['sourceElementId'],
            'target_structure_id' => $values['targetStructureId'],
            'target_element_id' => $values['targetElementId'],
            'mapping_type' => $values['mappingType'] ?? 'generates',
            'meta' => isset($values['meta']) ? json_encode($values['meta']) : null,
        ]);
    }

    /**
     * Get distinct target structure IDs that have mappings from a given source.
     *
     * @param  int  $sourceStructureId  Source structure ID
     * @return int[]
     */
    public function getTargetStructureIds(int $sourceStructureId): array
    {
        return $this->db->table('zp_work_structure_mappings')
            ->where('source_structure_id', $sourceStructureId)
            ->distinct()
            ->pluck('target_structure_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Check if a mapping exists.
     *
     * @param  int  $sourceStructureId  Source structure ID
     * @param  int  $sourceElementId  Source element ID
     * @param  int  $targetStructureId  Target structure ID
     */
    public function mappingExists(int $sourceStructureId, int $sourceElementId, int $targetStructureId): bool
    {
        return $this->db->table('zp_work_structure_mappings')
            ->where('source_structure_id', $sourceStructureId)
            ->where('source_element_id', $sourceElementId)
            ->where('target_structure_id', $targetStructureId)
            ->exists();
    }

    // ─── Hydrators ───────────────────────────────────────────────────

    /**
     * Hydrate a WorkStructure model from a database row.
     */
    private function hydrateStructure(object $row): WorkStructure
    {
        $model = new WorkStructure;
        $model->id = (int) $row->id;
        $model->title = $row->title;
        $model->description = $row->description ?? '';
        $model->type = $row->type;
        $model->createdBy = $row->created_by ? (int) $row->created_by : null;
        $model->meta = $row->meta;
        $model->createdAt = $row->created_at;
        $model->modifiedAt = $row->modified_at;

        return $model;
    }

    /**
     * Hydrate an ElementDefinition model from a database row.
     */
    private function hydrateElement(object $row): ElementDefinition
    {
        $model = new ElementDefinition;
        $model->id = (int) $row->id;
        $model->structureId = (int) $row->structure_id;
        $model->typeKey = $row->type_key;
        $model->label = $row->label;
        $model->description = $row->description ?? '';
        $model->domainReference = $row->domain_reference;
        $model->sortOrder = (int) $row->sort_order;
        $model->meta = $row->meta;
        $model->createdAt = $row->created_at;

        return $model;
    }

    /**
     * Hydrate a RelationshipDefinition model from a database row.
     */
    private function hydrateRelationship(object $row): RelationshipDefinition
    {
        $model = new RelationshipDefinition;
        $model->id = (int) $row->id;
        $model->structureId = (int) $row->structure_id;
        $model->fromElementId = (int) $row->from_element_id;
        $model->toElementId = (int) $row->to_element_id;
        $model->relationshipType = $row->relationship_type;
        $model->description = $row->description ?? '';
        $model->meta = $row->meta;

        return $model;
    }

    /**
     * Hydrate a StructureMapping model from a database row.
     */
    private function hydrateMapping(object $row): StructureMapping
    {
        $model = new StructureMapping;
        $model->id = (int) $row->id;
        $model->sourceStructureId = (int) $row->source_structure_id;
        $model->sourceElementId = (int) $row->source_element_id;
        $model->targetStructureId = (int) $row->target_structure_id;
        $model->targetElementId = (int) $row->target_element_id;
        $model->mappingType = $row->mapping_type;
        $model->meta = $row->meta;

        return $model;
    }
}
