<?php

namespace Unit\app\Domain\Users\Services;

use Illuminate\Http\Request;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Users\Services\Users;
use Unit\TestCase;

/**
 * The invite link sets the new account's password, so it is only built from the trusted app URL
 * and the email is not sent when no trusted URL is known.
 */
class InviteLinkTrustedUrlTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private array $sentHtml = [];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 5152, 'role' => 'owner', 'name' => 'Inviter', 'mail' => 'inviter@example.com']]);
        app()->instance('request', Request::create('http://evil.attacker.test/users/newUser', 'POST'));

        $this->sentHtml = [];
        app()->instance(MailerCore::class, $this->make(MailerCore::class, [
            'setContext' => fn () => null,
            'setSubject' => fn () => null,
            'setHtml' => function ($html) {
                $this->sentHtml[] = $html;
            },
            'sendMail' => fn () => true,
        ]));
    }

    private function makeService(): Users
    {
        return $this->make(Users::class, [
            'language' => $this->make(LanguageCore::class, ['__' => fn (string $index) => '%s %s %s']),
        ]);
    }

    public function test_invite_link_uses_the_trusted_url(): void
    {
        app()->instance(TrustedAppUrl::class, $this->make(TrustedAppUrl::class, ['get' => fn () => 'https://pm.example.com']));

        $this->assertTrue($this->makeService()->sendUserInvite('invite-code', 'new@example.com'));

        $this->assertCount(1, $this->sentHtml);
        $this->assertStringContainsString('https://pm.example.com/auth/userInvite/invite-code', $this->sentHtml[0]);
        $this->assertStringNotContainsString('evil.attacker.test', $this->sentHtml[0]);
    }

    public function test_invite_is_not_sent_without_a_trusted_url(): void
    {
        app()->instance(TrustedAppUrl::class, $this->make(TrustedAppUrl::class, ['get' => fn () => null]));

        $this->assertFalse($this->makeService()->sendUserInvite('invite-code', 'new@example.com'));
        $this->assertSame([], $this->sentHtml);
    }
}
