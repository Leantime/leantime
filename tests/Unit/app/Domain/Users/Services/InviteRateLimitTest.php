<?php

namespace Unit\app\Domain\Users\Services;

use Illuminate\Support\Facades\RateLimiter;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\UI\Theme as ThemeCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Users\Services\Users;
use Unit\TestCase;

/**
 * Regression guard for the invite-spam rate limit. When an inviter exceeds the per-user cap,
 * createUserInvite() must short-circuit and never reach the DB insert — proving the limiter is
 * the real backstop for every entry point (web, JSON-RPC, resend all funnel through here).
 */
class InviteRateLimitTest extends TestCase
{
    private const INVITER_ID = 4242;

    protected function setUp(): void
    {
        parent::setUp();

        // The array cache store persists within a single test run, so clear the keys this test
        // touches to keep it independent of ordering and of any prior limiter state.
        foreach ($this->limiterKeys() as $key) {
            RateLimiter::clear($key);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->limiterKeys() as $key) {
            RateLimiter::clear($key);
        }

        parent::tearDown();
    }

    public function test_create_user_invite_returns_false_and_skips_db_when_user_cap_exceeded(): void
    {
        session(['userdata' => ['id' => self::INVITER_ID, 'name' => 'Inviter', 'mail' => 'inviter@example.com']]);

        // Exhaust the per-user hourly cap (default 10) on the exact key the service computes.
        [$userKey] = $this->limiterKeys();
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit($userKey, 3600);
        }

        // The DB layer must never be touched once the cap is hit.
        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->expects($this->never())->method('addUser');

        $service = new Users(
            $userRepo,
            $this->createMock(LanguageCore::class),
            $this->createMock(ProjectRepository::class),
            $this->createMock(ClientRepository::class),
            $this->createMock(AuthService::class),
            $this->createMock(Files::class),
            $this->createMock(Avatarcreator::class),
            $this->createMock(SettingService::class),
            $this->createMock(ThemeCore::class),
            $this->createMock(ProjectService::class),
        );

        $result = $service->createUserInvite([
            'user' => 'newuser@example.com',
            'firstname' => 'New',
            'lastname' => 'User',
            'role' => '20',
        ]);

        $this->assertFalse($result, 'createUserInvite must return false once the invite cap is exceeded');
    }

    /**
     * The user + tenant limiter keys, computed exactly as Users::invitesRateLimited() does.
     *
     * @return array{0: string, 1: string}
     */
    private function limiterKeys(): array
    {
        $host = (defined('BASE_URL') ? parse_url(BASE_URL, PHP_URL_HOST) : null) ?: 'default';

        return [
            'invites:'.$host.':user:'.self::INVITER_ID,
            'invites:'.$host.':tenant',
        ];
    }
}
