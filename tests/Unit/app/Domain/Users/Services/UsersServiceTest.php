<?php

namespace Unit\app\Domain\Users\Services;

use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Unit\TestCase;

/**
 * Unit tests for the Users service helpers extracted during the
 * thin-controller refactor (saveModalDismissal).
 */
class UsersServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Users service with mocked dependencies, injecting the
     * provided (stubbed) repository so we can observe persistence calls.
     */
    private function makeService(UserRepository $userRepo): UserService
    {
        return new UserService(
            $userRepo,
            $this->make(LanguageCore::class),
            $this->make(ProjectRepository::class),
            $this->make(ClientRepository::class),
            $this->make(AuthService::class),
            $this->make(Files::class),
            $this->make(Avatarcreator::class),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata.id' => 1]);
        session()->forget('usersettings');
    }

    public function test_session_only_dismissal_records_session_without_persisting(): void
    {
        $persistCalls = 0;
        $repo = $this->make(UserRepository::class, [
            'patchUser' => function () use (&$persistCalls) {
                $persistCalls++;

                return true;
            },
        ]);

        $result = $this->makeService($repo)->saveModalDismissal('welcomeModal', false);

        $this->assertTrue($result);
        $this->assertSame(1, session('usersettings.modals.welcomeModal'));
        $this->assertSame(0, $persistCalls, 'A non-permanent dismissal must not touch the repository');
    }

    public function test_permanent_dismissal_persists_to_user_settings(): void
    {
        $persistCalls = 0;
        $repo = $this->make(UserRepository::class, [
            'patchUser' => function ($id, $params) use (&$persistCalls) {
                $persistCalls++;

                // The service must persist the serialized usersettings blob.
                $this->assertArrayHasKey('settings', $params);

                return true;
            },
        ]);

        $result = $this->makeService($repo)->saveModalDismissal('welcomeModal', true);

        $this->assertTrue($result);
        $this->assertSame('1', session('usersettings.modals.welcomeModal'));
        $this->assertSame(1, $persistCalls, 'A permanent dismissal must persist via the repository');
    }
}
