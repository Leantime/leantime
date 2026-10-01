<?php

namespace Unit\app\Core\UI;

use Leantime\Core\UI\Template;
use Unit\TestCase;

/**
 * Regression tests for Template::escape() (#3636).
 *
 * escape() used htmlentities(), which converts non-ASCII to named entities on top of the
 * XSS-relevant characters. Nearly every call site renders through {{ }}, which escapes the
 * resulting ampersand a second time, so users with non-English data saw a literal
 * "M&uuml;ller" in dropdowns, filters and Ideas.
 *
 * These pin both halves of the contract: non-ASCII survives, and the escaping is still as
 * strong as it was for the call sites that render through {!! !!}.
 */
class TemplateEscapeTest extends TestCase
{
    private function escape(?string $value): string
    {
        // Built without the constructor: escape() only reaches convertRelativePaths(), which
        // depends on the BASE_URL constant rather than any instance state, so none of
        // Template's collaborators (session, db, theme) need to exist here.
        $template = (new \ReflectionClass(Template::class))->newInstanceWithoutConstructor();

        return $template->escape($value);
    }

    public function test_non_ascii_is_left_alone(): void
    {
        $this->assertSame('Müller', $this->escape('Müller'));
        $this->assertSame('Ä Ö Ü ä ö ü ß', $this->escape('Ä Ö Ü ä ö ü ß'));
        $this->assertStringNotContainsString(
            '&uuml;',
            $this->escape('Müller'),
            'Umlauts must not be turned into named entities (#3636)'
        );
    }

    public function test_xss_relevant_characters_are_still_escaped(): void
    {
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $this->escape('<script>alert(1)</script>'));
        $this->assertSame('&quot; onerror=&quot;alert(1)', $this->escape('" onerror="alert(1)'));
        $this->assertSame('&#039; onmouseover=&#039;x', $this->escape("' onmouseover='x"));
        $this->assertSame('a &lt; b &amp; c &gt; d', $this->escape('a < b & c > d'));
    }

    public function test_ampersand_is_escaped_exactly_once(): void
    {
        // The double-escape the user actually saw came from & being encoded here and again
        // by Blade. One pass here must produce exactly one &amp;.
        $this->assertSame('Müller &amp; Söhne', $this->escape('Müller & Söhne'));
    }

    public function test_null_is_an_empty_string(): void
    {
        $this->assertSame('', $this->escape(null));
    }
}
