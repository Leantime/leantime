<?php

declare(strict_types=1);

namespace Leantime\Core\Support;

/**
 * Enum representing the available editor types in Leantime.
 *
 * Each case maps to a specific editor configuration with varying feature sets.
 */
enum EditorTypeEnum: string
{
    /**
     * Shows a simplified editor used for comments
     */
    case Simple = 'tiptapSimple';

    /**
     * Shows a more complex editor for entity descriptions
     */
    case Complex = 'tiptapComplex';

    /**
     * Shows the full editor with all features
     */
    case Notes = 'tiptapNotes';

}
