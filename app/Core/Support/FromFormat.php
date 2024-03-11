<?php

namespace Leantime\Core\Support;

/**
 * Enum class FromFormat
 *
 * An enumeration class representing various formats for date and time values.
 */
enum FromFormat {

    /**
     * For value containing date time string from database in UTC
     */
    case DbDate;

    /**
     * For value containing both date and time in users preferred format and timezone separated by a space
     */
    case UserDateTime;

    /**
     * for values containing only the user formatted date and timezone. Adds start of day time to string
     */
    case UserDateStartOfDay;

    /**
     * For values containing only the user formatted date and timezone. Adds end of day time to string
     */
    case UserDateEndOfDay;

}
