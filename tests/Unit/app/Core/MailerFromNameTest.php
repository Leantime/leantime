<?php

namespace Unit\app\Core;

use Leantime\Core\Mailer;

/**
 * LEAN_EMAIL_FROM_NAME sets the brand shown in the From display name; unset keeps "Leantime".
 */
class MailerFromNameTest extends \Unit\TestCase
{
    public function test_unset_from_name_keeps_leantime_default(): void
    {
        $this->assertSame('Leantime', Mailer::resolveFromBrandName(''));
    }

    public function test_configured_from_name_is_used(): void
    {
        $this->assertSame('Acme Projects', Mailer::resolveFromBrandName('  Acme Projects '));
    }

    public function test_configured_from_name_is_sanitized(): void
    {
        $this->assertSame('Acme', Mailer::resolveFromBrandName("<b>Acme</b>\r\n"));
        $this->assertSame('Leantime', Mailer::resolveFromBrandName('<script></script>'));
    }

    public function test_default_display_name_is_unchanged_without_brand(): void
    {
        $this->assertSame('Leantime', Mailer::buildFromDisplayName('Leantime', 'Leantime'));
        $this->assertSame('Leantime', Mailer::buildFromDisplayName('', 'Leantime'));
        $this->assertSame('My Project (Leantime)', Mailer::buildFromDisplayName('My Project', 'Leantime'));
    }

    public function test_brand_replaces_leantime_in_display_name(): void
    {
        $this->assertSame('Acme Projects', Mailer::buildFromDisplayName('Leantime', 'Acme Projects'));
        $this->assertSame('Acme Projects', Mailer::buildFromDisplayName('acme projects', 'Acme Projects'));
        $this->assertSame('My Project (Acme Projects)', Mailer::buildFromDisplayName('My Project', 'Acme Projects'));
    }

    public function test_caller_label_is_still_sanitized(): void
    {
        $this->assertSame('Acme Projects', Mailer::buildFromDisplayName('https://spam.example', 'Acme Projects'));
        $this->assertSame('Acme Projects', Mailer::buildFromDisplayName(null, 'Acme Projects'));
    }
}
