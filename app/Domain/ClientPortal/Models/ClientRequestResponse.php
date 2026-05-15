<?php

namespace Leantime\Domain\ClientPortal\Models;

/**
 * Represents a TL/CM response to a client request.
 */
class ClientRequestResponse
{
    public int $id = 0;

    public int $requestId = 0;

    public int $respondedByUserId = 0;

    public ?string $driveLink = null;

    public ?string $documentPath = null;

    public ?string $notes = null;

    public string $createdAt = '';
}
