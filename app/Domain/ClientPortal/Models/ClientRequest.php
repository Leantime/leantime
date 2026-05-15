<?php

namespace Leantime\Domain\ClientPortal\Models;

/**
 * Represents a client-submitted request for a new project addition.
 */
class ClientRequest
{
    public int $id = 0;

    public int $projectId = 0;

    public int $clientUserId = 0;

    public string $title = '';

    public string $description = '';

    public ?string $filePath = null;

    public string $status = 'open';

    public ?string $clientReviewAction = null;

    public ?string $clientReviewReason = null;

    public ?string $clientReviewedAt = null;

    public string $createdAt = '';
}
