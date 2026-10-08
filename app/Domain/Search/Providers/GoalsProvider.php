<?php

namespace Leantime\Domain\Search\Providers;

use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Repositories\Search as SearchRepository;
use Leantime\Domain\Search\Support\Highlighter;

/**
 * Goals: zp_canvas_items rows in box "goal" of a zp_canvas of type "goalcanvas".
 */
class GoalsProvider implements SearchProvider
{
    private const CANVAS_TYPES = ['goalcanvas'];

    private const BOX = 'goal';

    public function __construct(private SearchRepository $searchRepository) {}

    public function key(): string
    {
        return 'goals';
    }

    public function label(): string
    {
        return __('search.type.goals');
    }

    public function icon(): string
    {
        return 'fa-solid fa-bullseye';
    }

    public function available(): bool
    {
        return true;
    }

    /**
     * @return SearchResult[]
     */
    public function search(SearchQuery $query): array
    {
        $rows = $this->searchRepository->searchCanvasItems($query, self::CANVAS_TYPES, self::BOX, ['title', 'description', 'assumptions', 'tags']);

        $results = [];
        foreach ($rows as $row) {
            $results[] = new SearchResult(
                type: $this->key(),
                id: (int) $row['id'],
                title: (string) ($row['title'] ?: __('search.untitled')),
                snippet: Highlighter::snippet($row['description'] ?? null, $query->tokens),
                icon: $this->icon(),
                badge: (string) ($row['canvasTitle'] ?? ''),
                projectId: (int) $row['projectId'],
                projectName: (string) ($row['projectName'] ?? ''),
                modified: $row['modified'] ?? null,
                modalPath: '/goalcanvas/editCanvasItem/'.(int) $row['id'],
            );
        }

        return $results;
    }

    public function resolveTarget(int $id): ?SearchTarget
    {
        $goal = $this->searchRepository->getCanvasItemTarget($id, self::CANVAS_TYPES, self::BOX);

        if ($goal === null) {
            return null;
        }

        return new SearchTarget(url: '/goalcanvas/dashboard#/goalcanvas/editCanvasItem/'.$id, projectId: $goal['projectId']);
    }
}
