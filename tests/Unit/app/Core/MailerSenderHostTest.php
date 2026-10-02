<?php

namespace Unit\app\Core;

use Leantime\Core\Mailer;

/**
 * Without LEAN_EMAIL_RETURN the no-reply sender is derived from the configured app URL; the
 * client-controlled Host header is only a validated last resort.
 */
class MailerSenderHostTest extends \Unit\TestCase
{
    private ?string $hostBackup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hostBackup = $_SERVER['HTTP_HOST'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->hostBackup === null) {
            unset($_SERVER['HTTP_HOST']);
        } else {
            $_SERVER['HTTP_HOST'] = $this->hostBackup;
        }

        parent::tearDown();
    }

    public function test_configured_app_url_wins_over_host_header(): void
    {
        $_SERVER['HTTP_HOST'] = 'attacker.example';

        $this->assertSame('pm.example.com', Mailer::fallbackSenderHost('https://PM.example.com/leantime'));
    }

    public function test_valid_request_host_is_used_without_app_url(): void
    {
        $_SERVER['HTTP_HOST'] = 'pm.example.com:8080';

        $this->assertSame('pm.example.com', Mailer::fallbackSenderHost(''));
    }

    public function test_malformed_host_header_is_rejected(): void
    {
        $_SERVER['HTTP_HOST'] = "evil.example>\r\nBcc: victim@example.com";

        $this->assertSame('localhost', Mailer::fallbackSenderHost(''));
    }
}
