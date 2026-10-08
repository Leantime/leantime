<?php

namespace Leantime\Domain\Search\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Services\Search as SearchService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full search results page: GET /search/show?q=term
 *
 * Renders the page frame only; each entity type loads its own panel over HTMX so the
 * panels run in parallel and a slow type never blocks the others.
 */
class Show extends Controller
{
    private SearchService $searchService;

    public function init(SearchService $searchService): void
    {
        $this->searchService = $searchService;
    }

    /**
     * Render the results page.
     */
    public function get(array $params): Response
    {
        $term = SearchQuery::normalize((string) ($params['q'] ?? ''));

        $this->tpl->assign('term', $term);
        $this->tpl->assign('searchable', mb_strlen($term) >= SearchQuery::MIN_TERM_LENGTH);
        $this->tpl->assign('providers', $this->searchService->getProviders());

        return $this->tpl->display('search.show');
    }
}
