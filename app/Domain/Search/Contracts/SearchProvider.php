<?php

namespace Leantime\Domain\Search\Contracts;

use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;

/**
 * One searchable entity type (to-dos, projects, wiki articles, …).
 *
 * Core providers live in app/Domain/Search/Providers. Plugins add their own through the
 * `leantime.domain.search.services.search.getProviders.providers` filter. Every provider is
 * responsible for its own access rules: the SearchQuery carries the user's accessible project
 * ids (null for admins/owners), and entity-specific rules (wiki drafts, private notes, …)
 * are applied by the provider's query. Never trust a bare id: shared tables such as
 * zp_canvas_items need a type check on every lookup.
 */
interface SearchProvider
{
    /**
     * Stable key used in URLs and as the result type, e.g. "tickets".
     */
    public function key(): string;

    /**
     * Translated, human-readable group label, e.g. "To-Dos".
     */
    public function label(): string;

    /**
     * Font Awesome class for the group header, e.g. "fa-solid fa-list-check".
     */
    public function icon(): string;

    /**
     * Run the search for this entity type.
     *
     * @return SearchResult[]
     */
    public function search(SearchQuery $query): array;

    /**
     * Resolve where a result opens and which project it belongs to.
     *
     * Returns null when the entity does not exist or is not of this provider's type.
     */
    public function resolveTarget(int $id): ?SearchTarget;
}
