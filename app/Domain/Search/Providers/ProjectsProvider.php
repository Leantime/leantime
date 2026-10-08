<?php

namespace Leantime\Domain\Search\Providers;

use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Repositories\Search as SearchRepository;
use Leantime\Domain\Search\Support\Highlighter;

/**
 * Open projects, strategies and programs from zp_projects.
 */
class ProjectsProvider implements SearchProvider
{
    public function __construct(private SearchRepository $searchRepository) {}

    public function key(): string
    {
        return 'projects';
    }

    public function label(): string
    {
        return __('search.type.projects');
    }

    public function icon(): string
    {
        return 'fa-solid fa-diagram-project';
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
        $results = [];

        foreach ($this->searchRepository->searchProjects($query) as $row) {
            $type = (string) ($row['type'] ?? 'project');

            $results[] = new SearchResult(
                type: $this->key(),
                id: (int) $row['id'],
                title: (string) $row['name'],
                snippet: Highlighter::snippet($row['details'] ?? null, $query->tokens),
                icon: $this->icon(),
                badge: $this->badgeForType($type),
                projectId: (int) $row['id'],
                projectName: (string) ($row['clientName'] ?? ''),
                modified: $row['modified'] ?? null,
            );
        }

        return $results;
    }

    /**
     * Projects open through the existing switch endpoint, which authorizes itself and lets
     * plugins pick the landing page via the defaultProjectUrl filter.
     */
    public function resolveTarget(int $id): ?SearchTarget
    {
        return new SearchTarget(url: '/projects/changeCurrentProject/'.$id);
    }

    private function badgeForType(string $type): string
    {
        return match ($type) {
            'strategy' => __('search.badge.strategy'),
            'program' => __('search.badge.program'),
            default => '',
        };
    }
}
