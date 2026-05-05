<?php

namespace Leantime\Domain\Oneonone\Models;

/**
 * SessionItem - represents a single line item inside a 1:1 session
 * (talking point, action item, feedback, goal, blocker).
 */
class SessionItem
{
    public ?int $id = null;

    public ?int $sessionId = null;

    public string $type = 'talking_point';

    public ?int $author = null;

    public ?int $assignedTo = null;

    public ?string $content = null;

    public string $status = 'open';

    public ?string $dueDate = null;

    public int $sortIndex = 0;

    public ?int $linkedTicketId = null;

    public ?string $created = null;

    public ?string $modified = null;
}
