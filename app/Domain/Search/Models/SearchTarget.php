<?php

namespace Leantime\Domain\Search\Models;

/**
 * Where a search result opens. The Open controller authorizes against $projectId,
 * switches the session project when needed and redirects to $url.
 */
final class SearchTarget
{
    /**
     * @param  string  $url  Path relative to BASE_URL, e.g. "/dashboard/home#/tickets/showTicket/12".
     * @param  int|null  $projectId  Owning project to authorize and switch to; null when the
     *                               target URL performs its own authorization (project links).
     */
    public function __construct(
        public readonly string $url,
        public readonly ?int $projectId = null,
    ) {}
}
