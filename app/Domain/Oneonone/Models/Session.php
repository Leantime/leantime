<?php

namespace Leantime\Domain\Oneonone\Models;

/**
 * Session - represents a single 1:1 meeting between a manager and an employee.
 */
class Session
{
    public ?int $id = null;

    public ?int $employeeId = null;

    public ?int $managerId = null;

    public ?string $meetingDate = null;

    public ?string $title = null;

    public ?string $mood = null;

    public string $status = 'scheduled';

    public ?string $notes = null;

    public ?string $summary = null;

    public ?string $created = null;

    public ?string $modified = null;
}
