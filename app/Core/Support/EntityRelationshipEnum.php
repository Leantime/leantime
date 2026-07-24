<?php

namespace Leantime\Core\Support;

/**
 * Enum class EntityRelationshipEnum
 *
 * Represents various types of entity relationships.
 */
enum EntityRelationshipEnum: string
{
    /**
     * Represents a collaborator relationship between ticket and user.
     */
    case Collaborator = 'collaborator';

    /**
     * Represents a "generated from" relationship (entity created from a canvas item).
     */
    case GeneratedFrom = 'generated_from';

    /**
     * Represents a "maps to" relationship (cross-structure element mapping).
     */
    case MapsTo = 'maps_to';

    /**
     * Represents a "tracked by" relationship: a goal is tracked/measured by a
     * milestone. Direction convention: entityA = goal (GoalItem),
     * entityB = milestone (Ticket). The edge-based successor to the legacy
     * single zp_canvas_items.milestoneId column (which is retained during the
     * transition) so a goal can hold many milestones.
     */
    case TrackedBy = 'tracked_by';
}
