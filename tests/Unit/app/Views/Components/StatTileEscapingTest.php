<?php

namespace Unit\app\Views\Components;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Unit\TestCase;

/**
 * x-global::statTile is a shared component used across the project reports, the
 * strategy/program report decks and Resource Allocation. Its `sub` prop renders
 * a caption under the value.
 *
 * It originally rendered `sub` through {!! !!} so one caller (Resource
 * Allocation's budget tile) could emphasise an at-risk count with a <span>. That
 * left the prop unescaped for EVERY caller — a footgun that turns into stored
 * XSS the first time someone passes a project name or ticket headline through
 * it. Raised in review on #3771.
 *
 * It now escapes by default and lets a caller opt in per value with HtmlString.
 * These tests pin both halves of that contract.
 */
class StatTileEscapingTest extends TestCase
{
    private function render(array $data): string
    {
        return (string) Blade::render(
            '<x-global::statTile :value="$value" :label="$label" :sub="$sub" />',
            $data
        );
    }

    public function test_a_plain_string_sub_is_escaped(): void
    {
        $html = $this->render([
            'value' => 3,
            'label' => 'Milestones',
            'sub' => '<script>alert(1)</script>',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    /** The realistic vector: user-controlled entity names reaching a caption. */
    public function test_user_derived_text_in_sub_cannot_inject_markup(): void
    {
        $html = $this->render([
            'value' => 1,
            'label' => 'Overdue',
            'sub' => 'Project "><img src=x onerror=alert(1)>',
        ]);

        // The payload survives as escaped TEXT — that's fine and expected. What
        // matters is that no live tag is emitted, so assert on the delimiters
        // rather than on the substring, which is inert once escaped.
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
    }

    /** Opt-in markup still renders, so the at-risk emphasis keeps working. */
    public function test_htmlstring_sub_is_rendered_as_markup(): void
    {
        $html = $this->render([
            'value' => '$1,000',
            'label' => 'Budget',
            'sub' => new HtmlString('of $5,000 · <span class="risk">2 at-risk</span>'),
        ]);

        $this->assertStringContainsString('<span class="risk">2 at-risk</span>', $html);
    }

    /** Arrays of sub-lines follow the same rule, per element. */
    public function test_array_subs_are_escaped_per_line_and_htmlstring_still_passes(): void
    {
        $html = $this->render([
            'value' => 0,
            'label' => 'Allocated',
            'sub' => ['<b>plain</b>', new HtmlString('<span class="risk">marked</span>')],
        ]);

        $this->assertStringContainsString('&lt;b&gt;plain&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>plain</b>', $html);
        $this->assertStringContainsString('<span class="risk">marked</span>', $html);
    }

    /** A "0" sub-line is real content and must not be filtered out as falsy. */
    public function test_zero_is_kept_as_a_sub_line(): void
    {
        $html = $this->render([
            'value' => 5,
            'label' => 'Hours',
            'sub' => '0',
        ]);

        $this->assertStringContainsString('lt-stat-sub', $html);
    }
}
