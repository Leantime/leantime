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
     * Add other relationship types as needed.
     */
}
