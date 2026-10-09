<?php

namespace Leantime\Domain\Search\Providers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Repositories\Search as SearchRepository;
use Leantime\Domain\Search\Support\Highlighter;

/**
 * Strategy blueprints (SWOT, Lean Canvas, …): both the boards (zp_canvas) and their items.
 * Only canvas types the Blueprints template registry knows are searched, so the result can
 * always be routed to /blueprints/{slug}/showCanvas.
 *
 * Board results carry a negative id so one key can hold boards and items; see idFor().
 */
class BlueprintsProvider implements SearchProvider
{
    private const CANVAS_SUFFIX = 'canvas';

    private const BOARD_HITS = 5;

    public function __construct(
        private SearchRepository $searchRepository,
        private TemplateRegistry $templateRegistry,
        private PermissionService $permissions,
    ) {}

    public function key(): string
    {
        return 'blueprints';
    }

    public function label(): string
    {
        return __('search.type.blueprints');
    }

    public function icon(): string
    {
        return 'fa-solid fa-compass-drafting';
    }

    /**
     * The domain's view capability, checked against the user's global role: search spans every
     * accessible project, so a per-project custom role cannot be applied per hit.
     */
    public function available(): bool
    {
        return $this->canvasTypes() !== [] && $this->permissions->currentUserCan(BlueprintsPermissions::VIEW, null, true);
    }

    /**
     * Boards (at most BOARD_HITS) lead the first page and items fill the rest of the limit;
     * later pages are items only, with the offset shifted by the boards that took slots on
     * page one, so "load more" never skips or repeats an item.
     *
     * @return SearchResult[]
     */
    public function search(SearchQuery $query): array
    {
        $types = $this->canvasTypes();
        if ($types === []) {
            return [];
        }

        $boardQuery = new SearchQuery($query->term, $query->userId, $query->accessibleProjectIds, min(self::BOARD_HITS, $query->limit), 0, $query->filters);
        $boardRows = $this->searchRepository->searchCanvasBoards($boardQuery, $types);
        $boardCount = count($boardRows);

        $results = [];

        if ($query->offset === 0) {
            foreach ($boardRows as $row) {
                $results[] = new SearchResult(
                    type: $this->key(),
                    id: self::idFor('board', (int) $row['id']),
                    title: (string) ($row['title'] ?: __('search.untitled')),
                    snippet: Highlighter::snippet($row['description'] ?? null, $query->tokens),
                    icon: $this->icon(),
                    badge: $this->typeLabel((string) $row['canvasType']),
                    projectId: (int) $row['projectId'],
                    projectName: (string) ($row['projectName'] ?? ''),
                    modified: $row['modified'] ?? null,
                );
            }
        }

        $itemLimit = $query->offset === 0 ? $query->limit - $boardCount : $query->limit;
        if ($itemLimit <= 0) {
            return $results;
        }

        $itemQuery = new SearchQuery($query->term, $query->userId, $query->accessibleProjectIds, $itemLimit, max(0, $query->offset - $boardCount), $query->filters);

        foreach ($this->searchRepository->searchCanvasItems($itemQuery, $types, null, ['description', 'title', 'assumptions', 'data', 'conclusion', 'tags']) as $row) {
            $title = (string) ($row['title'] ?: Highlighter::snippet($row['description'] ?? null, [], 120));

            $results[] = new SearchResult(
                type: $this->key(),
                id: (int) $row['id'],
                title: $title !== '' ? $title : __('search.untitled'),
                snippet: Highlighter::snippet($row['description'] ?? null, $query->tokens),
                icon: 'fa-regular fa-note-sticky',
                badge: $this->typeLabel((string) $row['canvasType']).' · '.$row['canvasTitle'],
                projectId: (int) $row['projectId'],
                projectName: (string) ($row['projectName'] ?? ''),
                modified: $row['modified'] ?? null,
            );
        }

        return $results;
    }

    public function resolveTarget(int $id): ?SearchTarget
    {
        $types = $this->canvasTypes();

        if ($id < 0) {
            $board = $this->searchRepository->getCanvasBoardTarget(-$id, $types);
            if ($board === null) {
                return null;
            }

            return new SearchTarget(
                url: '/blueprints/'.$this->slugFor($board['canvasType']).'/showCanvas/'.(-$id),
                projectId: $board['projectId'],
            );
        }

        $item = $this->searchRepository->getCanvasItemTarget($id, $types);
        if ($item === null) {
            return null;
        }

        $slug = $this->slugFor($item['canvasType']);

        return new SearchTarget(
            url: '/blueprints/'.$slug.'/showCanvas/'.$item['canvasId'].'#/blueprints/'.$slug.'/editCanvasItem/'.$id,
            projectId: $item['projectId'],
        );
    }

    /**
     * Boards and items share one result type; boards are encoded as negative ids.
     */
    public static function idFor(string $kind, int $id): int
    {
        return $kind === 'board' ? -$id : $id;
    }

    /**
     * zp_canvas.type values for every registered blueprint ("swot" → "swotcanvas").
     *
     * @return string[]
     */
    private function canvasTypes(): array
    {
        return array_map(fn (string $slug) => $slug.self::CANVAS_SUFFIX, $this->templateRegistry->slugs());
    }

    private function slugFor(string $canvasType): string
    {
        return str_ends_with($canvasType, self::CANVAS_SUFFIX)
            ? substr($canvasType, 0, -strlen(self::CANVAS_SUFFIX))
            : $canvasType;
    }

    /**
     * Human label for a canvas type, falling back to the slug in upper case.
     */
    private function typeLabel(string $canvasType): string
    {
        $label = __('label.'.$canvasType);

        return $label !== 'label.'.$canvasType ? $label : strtoupper($this->slugFor($canvasType));
    }
}
