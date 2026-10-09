<?php

namespace Leantime\Domain\Search\Providers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Repositories\Search as SearchRepository;
use Leantime\Domain\Search\Support\Highlighter;
use Leantime\Domain\Wiki\Permissions\WikiPermissions;

/**
 * Wiki articles: zp_canvas_items rows in box "article" of a zp_canvas of type "wiki".
 * Drafts are visible to their author only.
 */
class WikiProvider implements SearchProvider
{
    private const CANVAS_TYPES = ['wiki'];

    private const BOX = 'article';

    public function __construct(
        private SearchRepository $searchRepository,
        private PermissionService $permissions,
    ) {}

    public function key(): string
    {
        return 'wiki';
    }

    public function label(): string
    {
        return __('search.type.wiki');
    }

    public function icon(): string
    {
        return 'fa-solid fa-book';
    }

    /**
     * The domain's view capability, checked against the user's global role: search spans every
     * accessible project, so a per-project custom role cannot be applied per hit.
     */
    public function available(): bool
    {
        return $this->permissions->currentUserCan(WikiPermissions::VIEW, null, true);
    }

    /**
     * @return SearchResult[]
     */
    public function search(SearchQuery $query): array
    {
        $rows = $this->searchRepository->searchCanvasItems($query, self::CANVAS_TYPES, self::BOX, publishedOrOwnDrafts: true);

        $results = [];
        foreach ($rows as $row) {
            $results[] = new SearchResult(
                type: $this->key(),
                id: (int) $row['id'],
                title: (string) ($row['title'] ?: __('search.untitled')),
                snippet: Highlighter::snippet($row['description'] ?? null, $query->tokens),
                icon: 'fa-solid fa-file-lines',
                badge: ($row['status'] ?? '') === 'draft' ? __('search.badge.draft') : '',
                projectId: (int) $row['projectId'],
                projectName: (string) ($row['projectName'] ?? ''),
                modified: $row['modified'] ?? null,
            );
        }

        return $results;
    }

    public function resolveTarget(int $id): ?SearchTarget
    {
        // Same visibility as the search itself, so a draft id is not an existence oracle.
        $article = $this->searchRepository->getCanvasItemTarget($id, self::CANVAS_TYPES, self::BOX, publishedOrOwnDraftsFor: (int) session('userdata.id'));

        if ($article === null) {
            return null;
        }

        return new SearchTarget(url: '/wiki/show/'.$id, projectId: $article['projectId']);
    }
}
