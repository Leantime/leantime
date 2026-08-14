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
     * Associates a goal with a milestone for informational display only — the
     * milestone(s) are rendered as a list on the goal; they do NOT drive goal
     * progress (progress stays metric-defined from the goal's own values).
     * Direction convention: entityA = goal (GoalItem), entityB = milestone
     * (Ticket). The edge-based successor to the legacy single
     * zp_canvas_items.milestoneId column (retained during the transition) so a
     * goal can hold many milestones.
     */
    case TrackedBy = 'tracked_by';
}
