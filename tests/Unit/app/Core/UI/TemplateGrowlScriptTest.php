<?php

namespace Unit\app\Core\UI;

use Leantime\Core\UI\Template;
use Unit\TestCase;

/**
 * Template::growlScript() puts a notification into an inline script. The text can carry user
 * data (a name in an error), so it must not be able to end the JS string or the script, and
 * must not run as HTML. Notifications that are meant to carry a link still keep it.
 */
class TemplateGrowlScriptTest extends TestCase
{
    private function growlScript(string $message, string $style = 'error'): string
    {
        // Built without the constructor, like TemplateEscapeMinimalTest: escapeMinimal() needs no state.
        $template = (new \ReflectionClass(Template::class))->newInstanceWithoutConstructor();

        return $template->growlScript($message, $style);
    }

    /**
     * The JS object literal inside the script tag, decoded the way the browser would read it.
     *
     * @return array{message: string, style: string}
     */
    private function decoded(string $script): array
    {
        $this->assertSame(1, preg_match('/^<script type="text\/javascript">jQuery\.growl\(\{message: (.*), style: (.*), html: true\}\);<\/script>$/s', $script, $m));

        return ['message' => json_decode($m[1], true, flags: JSON_THROW_ON_ERROR), 'style' => json_decode($m[2], true, flags: JSON_THROW_ON_ERROR)];
    }

    public function test_quotes_and_a_closing_script_tag_cannot_break_out(): void
    {
        $script = $this->growlScript('Bob "the" Builder</script><script>alert(1)</script>');

        // One script element, and nothing in it that the HTML parser would end early.
        $this->assertSame(1, substr_count($script, '</script>'));
        $this->assertSame(1, substr_count(strtolower($script), '<script'));

        $decoded = $this->decoded($script);
        $this->assertStringNotContainsString('<script', $decoded['message']);
        $this->assertStringContainsString('Bob', $decoded['message']);
    }

    public function test_event_handlers_and_javascript_links_are_removed(): void
    {
        $decoded = $this->decoded($this->growlScript('<img src=x onerror=alert(1)> <a href="javascript:alert(2)">x</a>'));

        $this->assertStringNotContainsString('onerror', $decoded['message']);
        // A javascript: link loses its scheme (it becomes a harmless relative link).
        $this->assertStringNotContainsString('href="javascript:', $decoded['message']);
    }

    public function test_escaped_text_stays_text_and_a_link_notification_keeps_its_link(): void
    {
        $escaped = htmlspecialchars("Ana <b>B</b> can't see the project", ENT_QUOTES, 'UTF-8');
        $this->assertStringContainsString('&lt;b&gt;', $this->decoded($this->growlScript($escaped))['message']);

        $linked = $this->decoded($this->growlScript("The project was copied successfully,<br/><a href='https://example.com/projects/1'>click here to open it</a>", 'success'));
        $this->assertStringContainsString('href="https://example.com/projects/1"', $linked['message']);
        $this->assertSame('success', $linked['style']);
    }

    public function test_the_style_is_reduced_to_a_class_name(): void
    {
        // growl puts the style into a single-quoted class attribute.
        $this->assertSame('successonmouseoveralert1data-x', $this->decoded($this->growlScript('Saved', "success' onmouseover='alert(1)' data-x='"))['style']);

        $script = $this->growlScript('Saved', 'success"});alert(1);//');
        $this->assertSame('successalert1', $this->decoded($script)['style']);
        $this->assertStringNotContainsString('alert(1)', $script);

        $this->assertSame('success', $this->decoded($this->growlScript('Saved', 'success'))['style']);
    }
}
