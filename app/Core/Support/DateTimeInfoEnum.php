<?php

namespace Leantime\Core\Support;

/**
 * Enum class FromFormat
 *
 * An enumeration class representing various formats for date and time values.
 */
enum DateTimeInfoEnum
{
    /**
     * Displays date with content:
     * Written On DATE at TIME
     */
    case WrittenOnAt;

    /**
     * Displays date with content:
     * Updated On DATE at TIME
     */
    case UpcatedOnAt;

    /**
     * Displays date with content:
     * XXX days/months ago
     */
    case HumanReadable;

    /**
     * Just displays DATE TIME
     */
    case Plain;

}
