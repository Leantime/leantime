<?php

namespace Leantime\Domain\Search\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Services\Search as SearchService;
use Symfony\Component\HttpFoundation\Response;

/**
 * One results panel on the full search page:
 * GET /hx/search/results/get?q=term&type=tickets&offset=0[&projectId=&from=&to=&mine=1]
 *
 * The first page renders the whole panel; "load more" requests (offset > 0) return only the
 * `rows` fragment, which replaces the load-more button in place.
 */
class Results extends HtmxController
{
    public const PAGE_SIZE = 20;

    protected static string $view = 'search::partials.resultsPanel';

    private SearchService $searchService;

    public function init(SearchService $searchService): void
    {
        $this->searchService = $searchService;
    }

    /**
     * Render a page of results for one provider.
     */
    public function get(array $params): string|Response|null
    {
        $type = (string) ($params['type'] ?? '');
        $provider = $this->searchService->getProvider($type);

        if ($provider === null) {
            return $this->tpl->displayPartial('errors.error404', responseCode: 404);
        }

        $term = SearchQuery::normalize((string) ($params['q'] ?? ''));
        $offset = max(0, (int) ($params['offset'] ?? 0));

        // Raw values travel on to the "load more" button; the service validates them.
        $rawFilters = array_filter([
            'projectId' => (string) ($params['projectId'] ?? ''),
            'from' => (string) ($params['from'] ?? ''),
            'to' => (string) ($params['to'] ?? ''),
            'mine' => (string) ($params['mine'] ?? ''),
        ], fn (string $value) => $value !== '');

        // One row beyond the page tells us whether a next page exists, independent of how a
        // provider fills its slots (exact-id hits, blueprint boards, de-duplication).
        $results = $this->searchService->search($term, $type, self::PAGE_SIZE + 1, $offset, $rawFilters);
        $hasMore = count($results) > self::PAGE_SIZE;
        $results = array_slice($results, 0, self::PAGE_SIZE);

        $this->tpl->assign('provider', $provider);
        $this->tpl->assign('term', $term);
        $this->tpl->assign('tokens', SearchQuery::tokenize($term));
        $this->tpl->assign('results', $results);
        $this->tpl->assign('offset', $offset);
        $this->tpl->assign('nextOffset', $offset + self::PAGE_SIZE);
        $this->tpl->assign('hasMore', $hasMore);
        $this->tpl->assign('filters', $rawFilters);

        return $offset > 0 ? 'rows' : null;
    }
}
