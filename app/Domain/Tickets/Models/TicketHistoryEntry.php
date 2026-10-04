<?php

namespace Leantime\Domain\Tickets\Models;

/**
 * One recorded field change of a ticket, prepared for display.
 *
 * Values are plain (unescaped) display strings; templates must escape them. oldValue is null when
 * the previous value is unknown (the history table only records new values, so the first recorded
 * change of a field has no "from"). Description changes carry no values at all, only the flag.
 */
class TicketHistoryEntry
{
    /**
     * @param  int  $id  zp_tickethistory row id
     * @param  int|null  $userId  user who made the change
     * @param  string  $userName  display name of that user
     * @param  string  $dateModified  UTC database datetime (Y-m-d H:i:s)
     * @param  string  $field  the raw changeType (headline, status, editors, ...)
     * @param  string  $fieldLabel  translated field label
     * @param  string|null  $oldValue  previous display value, null when unknown
     * @param  string|null  $newValue  new display value
     * @param  bool  $isDescriptionChange  true for description edits (values are not shown)
     */
    public function __construct(
        public readonly int $id,
        public readonly ?int $userId,
        public readonly string $userName,
        public readonly string $dateModified,
        public readonly string $field,
        public readonly string $fieldLabel,
        public readonly ?string $oldValue,
        public readonly ?string $newValue,
        public readonly bool $isDescriptionChange = false,
    ) {}
}
