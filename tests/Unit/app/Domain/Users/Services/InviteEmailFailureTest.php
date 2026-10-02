<?php

namespace Unit\app\Domain\Users\Services;

use Illuminate\Support\Facades\RateLimiter;
use Leantime\Domain\Users\Services\Users;
use Unit\TestCase;

/**
 * #1795: with broken SMTP settings the invitation email fails, but the admin was told
 * "New user invited successfully" / "The invitation was sent successfully". The user still
 * exists (and can be re-invited), so the flow must say the EMAIL failed.
 */
class InviteEmailFailureTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const INVITER_ID = 5151;

    protected function setUp(): void
    {
        parent::setUp();
        session(['userdata' => ['id' => self::INVITER_ID, 'name' => 'Inviter', 'mail' => 'inviter@example.com']]);
        RateLimiter::clear('invites:'.BASE_URL.':user:'.self::INVITER_ID);
        RateLimiter::clear('invites:'.BASE_URL.':tenant');
    }

    public function test_new_user_invite_reports_a_failed_email(): void
    {
        $service = $this->make(Users::class, [
            'usernameExist' => fn () => false,
            'createUserInviteWithStatus' => fn () => ['userId' => '77', 'emailSent' => false],
        ]);

        $result = $service->inviteNewUser(['user' => 'new@example.com', 'firstname' => 'New', 'lastname' => 'User', 'role' => '20'], null, false);

        $this->assertSame('invite_email_failed', $result);
    }

    public function test_new_user_invite_still_reports_success_when_the_email_left(): void
    {
        $service = $this->make(Users::class, [
            'usernameExist' => fn () => false,
            'createUserInviteWithStatus' => fn () => ['userId' => '77', 'emailSent' => true],
        ]);

        $this->assertSame('success', $service->inviteNewUser(['user' => 'new@example.com', 'role' => '20'], null, false));
    }

    public function test_resend_invite_reports_a_failed_email(): void
    {
        $service = $this->make(Users::class, [
            'sendUserInvite' => fn () => false,
        ]);

        $result = $service->resendUserInvite(91, ['username' => 'pending@example.com', 'pwReset' => 'existing-code']);

        $this->assertSame('invite_email_failed', $result);
    }
}
