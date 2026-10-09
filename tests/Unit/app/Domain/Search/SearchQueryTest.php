<?php

namespace Unit\app\Domain\Search;

use Leantime\Domain\Search\Models\SearchQuery;

/**
 * Term normalization, tokenizing, wildcard escaping and scope flags of SearchQuery.
 */
class SearchQueryTest extends \Unit\TestCase
{
    public function test_term_is_whitespace_normalized(): void
    {
        $query = new SearchQuery("  fix   login\tbug \n", userId: 1, accessibleProjectIds: null);

        $this->assertSame('fix login bug', $query->term);
        $this->assertSame(['fix', 'login', 'bug'], $query->tokens);
    }

    public function test_tokens_are_distinct_and_capped(): void
    {
        $query = new SearchQuery('a b a c d e f g', userId: 1, accessibleProjectIds: null);

        $this->assertSame(['a', 'b', 'c', 'd', 'e'], $query->tokens);
    }

    public function test_short_terms_are_not_searchable(): void
    {
        $this->assertFalse((new SearchQuery('a', userId: 1, accessibleProjectIds: null))->isSearchable());
        $this->assertFalse((new SearchQuery('   ', userId: 1, accessibleProjectIds: null))->isSearchable());
        $this->assertTrue((new SearchQuery('ab', userId: 1, accessibleProjectIds: null))->isSearchable());
    }

    public function test_like_wildcards_are_escaped_literally(): void
    {
        $query = new SearchQuery('100%_done\\', userId: 1, accessibleProjectIds: null);

        $this->assertSame('%100\\%\\_done\\\\%', $query->containsPattern($query->tokens[0]));
        $this->assertSame('100\\%\\_done\\\\%', $query->prefixPattern($query->tokens[0]));
    }

    public function test_limit_and_offset_are_clamped(): void
    {
        $query = new SearchQuery('term', userId: 1, accessibleProjectIds: null, limit: 500, offset: -10);

        $this->assertSame(SearchQuery::MAX_LIMIT, $query->limit);
        $this->assertSame(0, $query->offset);
        $this->assertSame(1, (new SearchQuery('term', userId: 1, accessibleProjectIds: null, limit: 0))->limit);
    }

    public function test_full_text_tokens_strip_operators_and_require_prefix_match(): void
    {
        $query = new SearchQuery('sprint+ "release" @v2x', userId: 1, accessibleProjectIds: null);

        $this->assertSame(['sprint', 'release', 'v2x'], $query->fullTextTokens());
        $this->assertSame('+sprint* +release* +v2x*', $query->booleanModeQuery());
    }

    public function test_default_stopwords_are_dropped_from_the_full_text_query(): void
    {
        $query = new SearchQuery('the roadmap for Q4x', userId: 1, accessibleProjectIds: null);

        $this->assertSame(['roadmap', 'Q4x'], $query->fullTextTokens());
        $this->assertSame('+roadmap* +Q4x*', $query->booleanModeQuery());
        $this->assertNull((new SearchQuery('the with', userId: 1, accessibleProjectIds: null))->fullTextTokens());
    }

    public function test_short_tokens_force_the_like_path(): void
    {
        $this->assertNull((new SearchQuery('go live', userId: 1, accessibleProjectIds: null))->fullTextTokens());
        $this->assertNull((new SearchQuery('c++', userId: 1, accessibleProjectIds: null))->fullTextTokens());
        $this->assertNull((new SearchQuery('foo-bar', userId: 1, accessibleProjectIds: null))->fullTextTokens());
        $this->assertNull((new SearchQuery('roadmap e-mail', userId: 1, accessibleProjectIds: null))->fullTextTokens());
        $this->assertSame('', (new SearchQuery('c++', userId: 1, accessibleProjectIds: null))->booleanModeQuery());
        $this->assertSame(['abc'], (new SearchQuery('abc', userId: 1, accessibleProjectIds: null))->fullTextTokens());
    }

    public function test_filters_are_readable_with_default(): void
    {
        $query = new SearchQuery('term', userId: 1, accessibleProjectIds: null, filters: ['projectId' => 7]);

        $this->assertSame(7, $query->filter('projectId'));
        $this->assertNull($query->filter('missing'));
        $this->assertSame('x', $query->filter('missing', 'x'));
    }
}
