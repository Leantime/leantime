<?php

namespace Unit\app\Domain\Search;

use Leantime\Domain\Search\Support\Highlighter;

/**
 * Snippet extraction and <mark> highlighting must escape output and never match inside entities.
 */
class HighlighterTest extends \Unit\TestCase
{
    public function test_mark_wraps_matches_case_insensitively(): void
    {
        $this->assertSame(
            'Fix the <mark>Login</mark> page <mark>login</mark> flow',
            Highlighter::mark('Fix the Login page login flow', ['login'])
        );
    }

    public function test_mark_escapes_html_and_keeps_matching_on_raw_text(): void
    {
        $this->assertSame(
            '&lt;b&gt;<mark>amp</mark>&lt;/b&gt; &amp; more',
            Highlighter::mark('<b>amp</b> & more', ['amp'])
        );
    }

    public function test_mark_without_tokens_only_escapes(): void
    {
        $this->assertSame('a &lt; b', Highlighter::mark('a < b', []));
        $this->assertSame('', Highlighter::mark('', ['x']));
    }

    public function test_mark_treats_tokens_literally(): void
    {
        $this->assertSame('100<mark>%</mark> (done)', Highlighter::mark('100% (done)', ['%']));
        $this->assertSame('<mark>(done)</mark>', Highlighter::mark('(done)', ['(done)']));
    }

    public function test_snippet_strips_html_and_collapses_whitespace(): void
    {
        $this->assertSame(
            'Hello world, this is bold.',
            Highlighter::snippet("<p>Hello&nbsp;world,</p>\n<p>this is <strong>bold</strong>.</p>", ['x'])
        );
    }

    public function test_snippet_centres_on_first_match_and_adds_ellipses(): void
    {
        $text = str_repeat('filler ', 40).'NEEDLE here '.str_repeat('tail ', 40);
        $snippet = Highlighter::snippet($text, ['needle'], 60);

        $this->assertStringStartsWith('…', $snippet);
        $this->assertStringEndsWith('…', $snippet);
        $this->assertStringContainsStringIgnoringCase('needle', $snippet);
        $this->assertLessThanOrEqual(62, mb_strlen($snippet));
    }

    public function test_snippet_handles_empty_input(): void
    {
        $this->assertSame('', Highlighter::snippet(null, ['x']));
        $this->assertSame('', Highlighter::snippet('<p></p>', ['x']));
    }
}
