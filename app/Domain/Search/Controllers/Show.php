<?php

namespace Leantime\Domain\Search\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Services\Search as SearchService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full search results page: GET /search/show?q=term[&types[]=tickets&projectId=&from=&to=&mine=1]
 *
 * Renders the page frame and the filter bar only; each entity type loads its own panel over
 * HTMX so the panels run in parallel and a slow type never blocks the others.
 */
class Show extends Controller
{
    private SearchService $searchService;

    private ProjectService $projectService;

    public function init(SearchService $searchService, ProjectService $projectService): void
    {
        $this->searchService = $searchService;
        $this->projectService = $projectService;
    }

    /**
     * Render the results page.
     */
    public function get(array $params): Response
    {
        $term = SearchQuery::normalize((string) ($params['q'] ?? ''));
        $providers = $this->searchService->getProviders();

        $requestedTypes = array_values(array_intersect((array) ($params['types'] ?? []), array_keys($providers)));
        $selectedTypes = $requestedTypes !== [] ? $requestedTypes : array_keys($providers);

        $rawFilters = [
            'projectId' => (string) ($params['projectId'] ?? ''),
            'from' => (string) ($params['from'] ?? ''),
            'to' => (string) ($params['to'] ?? ''),
            'mine' => (string) ($params['mine'] ?? ''),
        ];
        $normalized = SearchService::normalizeFilters($rawFilters);

        // Echo back only what validated, so the form never shows a value the search ignored.
        $filterValues = [
            'projectId' => $normalized['projectId'] ?? 0,
            'from' => isset($normalized['from']) ? $rawFilters['from'] : '',
            'to' => isset($normalized['to']) ? $rawFilters['to'] : '',
            'mine' => isset($normalized['mine']),
        ];

        $this->tpl->assign('term', $term);
        $this->tpl->assign('searchable', mb_strlen($term) >= SearchQuery::MIN_TERM_LENGTH);
        $this->tpl->assign('providers', $providers);
        $this->tpl->assign('selectedTypes', $selectedTypes);
        $this->tpl->assign('filterValues', $filterValues);
        $this->tpl->assign('projects', $this->openAccessibleProjects());

        return $this->tpl->display('search.show');
    }

    /**
     * Open projects the user may search, for the project filter.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function openAccessibleProjects(): array
    {
        $projects = $this->projectService->getProjectsUserHasAccessTo() ?: [];

        $open = array_filter($projects, fn (array $project) => (int) ($project['state'] ?? 0) !== -1);

        return array_map(fn (array $project) => ['id' => (int) $project['id'], 'name' => (string) $project['name']], array_values($open));
    }
}
