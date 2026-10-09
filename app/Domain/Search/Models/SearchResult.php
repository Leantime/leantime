<?php

namespace Leantime\Domain\Search\Models;

/**
 * One row in the search results. Templates render this and nothing else, so a provider
 * decides here what a hit looks like.
 */
final class SearchResult
{
    /**
     * @param  string  $type  Provider key the result belongs to.
     * @param  int  $id  Entity id within that type.
     * @param  string  $title  Plain text title (escaped and highlighted by the template).
     * @param  string  $snippet  Plain text excerpt around the first match, may be empty.
     * @param  string  $icon  Font Awesome class shown in front of the row.
     * @param  string  $badge  Translated qualifier shown in the meta line (e.g. "Milestone"), may be empty.
     * @param  int|null  $projectId  Owning project, null for entities without one.
     * @param  string  $projectName  Owning project's name, may be empty.
     * @param  string|null  $modified  Last-modified timestamp as stored (UTC database format).
     * @param  string|null  $modalPath  Hash route that opens the entity in a modal when the user is already
     *                                  in its project (e.g. "/tickets/showTicket/12"); null to always redirect.
     */
    public function __construct(
        public readonly string $type,
        public readonly int $id,
        public readonly string $title,
        public readonly string $snippet = '',
        public readonly string $icon = '',
        public readonly string $badge = '',
        public readonly ?int $projectId = null,
        public readonly string $projectName = '',
        public readonly ?string $modified = null,
        public readonly ?string $modalPath = null,
    ) {}
}
