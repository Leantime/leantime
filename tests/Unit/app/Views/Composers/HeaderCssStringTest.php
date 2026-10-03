<?php

namespace Unit\app\Views\Composers;

use Leantime\Views\Composers\Header;
use Unit\TestCase;

/**
 * The theme background URL is printed into a raw <style> element, where HTML escaping does not
 * apply. It must be emitted as a CSS string that cannot break out of url("...") or </style>.
 */
class HeaderCssStringTest extends TestCase
{
    public function test_a_normal_url_is_kept_readable(): void
    {
        $this->assertSame('"https://example.com/img/bg.png?v=2&x=1"', Header::toCssString('https://example.com/img/bg.png?v=2&x=1'));
    }

    public function test_breakout_characters_are_css_escaped(): void
    {
        $css = Header::toCssString('x");}</style><script>alert(1)</script>');

        $this->assertStringNotContainsString('<', $css);
        $this->assertStringNotContainsString(')', $css);
        $this->assertStringNotContainsString(';', $css);
        $this->assertSame(2, substr_count($css, '"'), 'only the delimiting quotes remain');
        $this->assertStringContainsString('\\3c ', $css);
    }

    public function test_multibyte_characters_are_escaped_as_code_points(): void
    {
        $this->assertSame('"a\\e9 "', Header::toCssString('aé'));
    }
}
