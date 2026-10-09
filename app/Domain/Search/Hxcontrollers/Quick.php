<?php

namespace Leantime\Domain\Search\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Services\Search as SearchService;

/**
 * Header quick-search dropdown: GET /hx/search/quick/get?q=term
 */
class Quick extends HtmxController
{
    protected static string $view = 'search::partials.quick';

    private SearchService $searchService;

    public function init(SearchService $searchService): void
    {
        $this->searchService = $searchService;
    }

    /**
     * Render the grouped top hits for the term.
     */
    public function get(array $params): void
    {
        $term = SearchQuery::normalize((string) ($params['q'] ?? ''));

        $this->tpl->assign('term', $term);
        $this->tpl->assign('tokens', SearchQuery::tokenize($term));
        $this->tpl->assign('searchable', mb_strlen($term) >= SearchQuery::MIN_TERM_LENGTH);
        $this->tpl->assign('providers', $this->searchService->getProviders());
        $this->tpl->assign('groups', $this->searchService->quickSearch($term));
    }
}
