<?php

namespace Unit\app\Core\Support;

use Leantime\Core\Support\NameSanitizer;
use Unit\TestCase;

/**
 * Regression tests for the invite-spam abuse fix: person names were stored and
 * emailed raw, letting spammers use the firstname field as an email payload.
 * The sanitizer must strip abuse vectors (contact numbers, URLs, emails, bidi
 * tricks) while letting legitimate names in any script through unchanged.
 */
class NameSanitizerTest extends TestCase
{
    public function test_legitimate_names_pass_unchanged(): void
    {
        $this->assertSame('Marcel', NameSanitizer::clean('Marcel'));
        $this->assertSame('María José', NameSanitizer::clean('María José'));
        $this->assertSame('汪小明', NameSanitizer::clean('汪小明'));
        $this->assertSame('محمد علي', NameSanitizer::clean('محمد علي'));
        $this->assertSame("O'Connor-Smith", NameSanitizer::clean("O'Connor-Smith"));
    }

    public function test_strips_contact_number_from_spam_payload(): void
    {
        // The actual payload from the 2026-07 abuse reports
        $this->assertStringNotContainsString('992600898', NameSanitizer::clean('+汪汪992600898-ن颂58嗏،Virtual'));
    }

    public function test_strips_html(): void
    {
        $this->assertSame('alert(1)', NameSanitizer::clean('<script>alert(1)</script>'));
    }

    public function test_strips_urls_and_emails(): void
    {
        $this->assertSame('Buy cheap', NameSanitizer::clean('Buy http://spam.example.com cheap'));
        $this->assertSame('Visit now', NameSanitizer::clean('Visit www.spam.example now'));
        $this->assertSame('mail me', NameSanitizer::clean('mail spam@evil.example me'));
    }

    public function test_strips_control_and_bidi_characters(): void
    {
        $this->assertSame('JohnSmith', NameSanitizer::clean("John\u{202E}Smith"));
        $this->assertSame('AB', NameSanitizer::clean("A\u{200B}\u{0000}B"));
    }

    public function test_caps_length_and_handles_non_strings(): void
    {
        $this->assertSame(50, mb_strlen(NameSanitizer::clean(str_repeat('A', 200))));
        $this->assertSame('', NameSanitizer::clean(null));
        $this->assertSame('', NameSanitizer::clean(12345));
        $this->assertSame('', NameSanitizer::clean(['array']));
    }

    public function test_collapses_whitespace(): void
    {
        $this->assertSame('John Smith', NameSanitizer::clean("  John   Smith \n"));
    }
}
