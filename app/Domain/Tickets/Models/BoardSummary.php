<?php

namespace Leantime\Domain\Tickets\Models;

use Carbon\CarbonImmutable;

/**
 * At-a-glance metrics for a task board, computed from the tickets currently on
 * screen (so the numbers always reflect the active filters). Rendered in the
 * page-header sub-line.
 */
class BoardSummary
{
    /** Total tasks on the board. */
    public int $total = 0;

    /** Tasks with no assignee (editorId). */
    public int $unassigned = 0;

    /** Tasks whose due date falls between today and the end of the current week. */
    public int $dueThisWeek = 0;

    /** Most recent change on the board (max ticket modified date), null if none. */
    public ?CarbonImmutable $lastUpdated = null;
}
