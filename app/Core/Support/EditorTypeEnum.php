<?php

namespace Leantime\Core\Support;

/**
 * Enum class FromFormat
 *
 * An enumeration class representing various formats for date and time values.
 */
enum EditorTypeEnum: string
{
    /**
     * Shows a simplified editor used for comments
     */
    case Simple = 'tinymceSimple';

    /**
     * Shows a more complex editor for entity descriptions
     */
    case Complex = 'tinymceComplex';

    /**
     * Shows the full editor with all featuers
     */
    case Notes = 'tinymceNotes';

}
