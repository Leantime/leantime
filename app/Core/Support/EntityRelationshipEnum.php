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
     * Represents a "generated from" relationship (entity created from canvas item).
     */
    case GeneratedFrom = 'generated_from';

    /**
     * Represents a "maps to" relationship (cross-structure element mapping).
     */
    case MapsTo = 'maps_to';
}
