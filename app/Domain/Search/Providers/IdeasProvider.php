<?php

namespace Leantime\Domain\Search\Providers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Ideas\Permissions\IdeasPermissions;
use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Repositories\Search as SearchRepository;
use Leantime\Domain\Search\Support\Highlighter;

/**
 * Ideas: zp_canvas_items rows of a zp_canvas of type "idea". The headline is stored in
 * `description`, the body in `data`, and the status in `box`.
 */
class IdeasProvider implements SearchProvider
{
    private const CANVAS_TYPES = ['idea'];

    public function __construct(
        private SearchRepository $searchRepository,
        private PermissionService $permissions,
    ) {}

    public function key(): string
    {
        return 'ideas';
    }

    public function label(): string
    {
        return __('search.type.ideas');
    }

    public function icon(): string
    {
        return 'fa-solid fa-lightbulb';
    }

    /**
     * The domain's view capability, checked against the user's global role: search spans every
     * accessible project, so a per-project custom role cannot be applied per hit.
     */
    public function available(): bool
    {
        return $this->permissions->currentUserCan(IdeasPermissions::VIEW, null, true);
    }

    /**
     * @return SearchResult[]
     */
    public function search(SearchQuery $query): array
    {
        $rows = $this->searchRepository->searchCanvasItems($query, self::CANVAS_TYPES, null, ['description', 'data', 'tags']);

        $results = [];
        foreach ($rows as $row) {
            $results[] = new SearchResult(
                type: $this->key(),
                id: (int) $row['id'],
                title: Highlighter::snippet($row['description'] ?? null, [], 120) ?: __('search.untitled'),
                snippet: Highlighter::snippet($row['data'] ?? null, $query->tokens),
                icon: $this->icon(),
                badge: (string) ($row['canvasTitle'] ?? ''),
                projectId: (int) $row['projectId'],
                projectName: (string) ($row['projectName'] ?? ''),
                modified: $row['modified'] ?? null,
                modalPath: '/ideas/ideaDialog/'.(int) $row['id'],
            );
        }

        return $results;
    }

    public function resolveTarget(int $id): ?SearchTarget
    {
        $idea = $this->searchRepository->getCanvasItemTarget($id, self::CANVAS_TYPES);

        if ($idea === null) {
            return null;
        }

        return new SearchTarget(url: '/ideas/showBoards#/ideas/ideaDialog/'.$id, projectId: $idea['projectId']);
    }
}
