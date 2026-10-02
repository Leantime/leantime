<?php

namespace Unit\app\Core\UI;

use Leantime\Core\UI\Template;
use Unit\TestCase;

/**
 * Regression tests for Template::escapeMinimal(), the rich-text sanitizer.
 *
 * Pins three things: htmx attributes never survive (htmx would act on them), embed
 * containers keep only https sources, and ordinary editor markup (task lists, mentions,
 * tables, links, images) still renders unchanged.
 */
class TemplateEscapeMinimalTest extends TestCase
{
    private function escapeMinimal(?string $value): string
    {
        // Built without the constructor: escapeMinimal() only reaches convertRelativePaths(),
        // which depends on the BASE_URL constant rather than any instance state.
        $template = (new \ReflectionClass(Template::class))->newInstanceWithoutConstructor();

        return $template->escapeMinimal($value);
    }

    public function test_data_prefixed_htmx_attributes_are_removed(): void
    {
        $output = $this->escapeMinimal('<div class="note" data-hx-on-click="alert(1)" data-hx-get="/x" data-hx-trigger="load">text</div>');

        $this->assertStringNotContainsString('hx-', $output);
        $this->assertStringContainsString('class="note"', $output);
        $this->assertStringContainsString('text', $output);
    }

    public function test_htmx_attributes_on_custom_elements_are_removed(): void
    {
        // htmLawed accepts any attribute on custom elements, so these reach the hook.
        $output = $this->escapeMinimal('<my-widget hx-get="/x" hx-trigger="load" hx-on:click="alert(1)">y</my-widget>');

        $this->assertStringNotContainsString('hx-', $output);
        $this->assertStringContainsString('y', $output);
    }

    public function test_event_handlers_and_scripts_are_still_removed(): void
    {
        $output = $this->escapeMinimal('<img src="https://example.com/a.png" onerror="alert(1)"><script>alert(2)</script>');

        $this->assertStringNotContainsString('onerror', $output);
        $this->assertStringNotContainsString('<script', $output);
    }

    public function test_embed_with_javascript_source_loses_the_source(): void
    {
        $output = $this->escapeMinimal('<p>ok</p><div data-embed data-src="javascript:alert(parent.document.domain)" data-type="googleDocs">embed</div>');

        $this->assertStringNotContainsString('javascript:', $output);
        $this->assertStringNotContainsString('data-src', $output);
        $this->assertStringContainsString('<p>ok</p>', $output);
    }

    public function test_embed_with_entity_encoded_javascript_source_loses_the_source(): void
    {
        $output = $this->escapeMinimal('<div data-embed data-src="java&#115;cript&#58;alert(1)" data-type="youtube">e</div>');

        $this->assertStringNotContainsString('data-src', $output);
    }

    public function test_embed_with_https_source_is_kept(): void
    {
        $output = $this->escapeMinimal('<div data-embed="" data-src="https://www.youtube.com/embed/abcdefghijk" data-type="youtube"></div>');

        $this->assertStringContainsString('data-src="https://www.youtube.com/embed/abcdefghijk"', $output);
        $this->assertStringContainsString('data-type="youtube"', $output);
    }

    public function test_editor_markup_is_preserved(): void
    {
        $input = '<ul data-type="taskList"><li data-checked="true">done</li></ul>'
            .'<span data-type="mention" data-id="3" data-label="Bob">@Bob</span>'
            .'<table><tr><td colspan="2" data-colwidth="100">cell</td></tr></table>'
            .'<p><a href="https://example.com">link</a><br /><strong>bold</strong></p>';

        $output = $this->escapeMinimal($input);

        $this->assertStringContainsString('<ul data-type="taskList"><li data-checked="true">done</li></ul>', $output);
        $this->assertStringContainsString('<span data-type="mention" data-id="3" data-label="Bob">@Bob</span>', $output);
        $this->assertStringContainsString('<td colspan="2" data-colwidth="100">cell</td>', $output);
        $this->assertStringContainsString('<a href="https://example.com">link</a><br />', $output);
        $this->assertStringContainsString('<strong>bold</strong>', $output);
    }

    public function test_oversized_tag_is_rendered_as_text_quickly(): void
    {
        // A single tag with thousands of attributes is quadratic for htmLawed's parser.
        $input = '<p'.str_repeat(' a=b', 16000).'>x</p>';

        $startedAt = microtime(true);
        $output = $this->escapeMinimal($input);
        $elapsedSeconds = microtime(true) - $startedAt;

        $this->assertStringStartsWith('&lt;p', $output);
        $this->assertLessThan(0.5, $elapsedSeconds);
    }

    public function test_content_over_the_size_limit_is_fully_escaped(): void
    {
        $input = '<b>'.str_repeat('a', 1048577).'</b>';

        $output = $this->escapeMinimal($input);

        $this->assertStringStartsWith('&lt;b&gt;', $output);
    }

    public function test_repeated_content_returns_the_same_result(): void
    {
        $input = '<p>same <em>content</em></p>';

        $this->assertSame($this->escapeMinimal($input), $this->escapeMinimal($input));
    }

    public function test_null_is_an_empty_string(): void
    {
        $this->assertSame('', $this->escapeMinimal(null));
    }
}
