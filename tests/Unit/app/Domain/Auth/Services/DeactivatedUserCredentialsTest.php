<?php

namespace Unit\app\Domain\Auth\Services;

use Illuminate\Session\SessionManager;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Api\Repositories\Api as ApiRepository;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Auth\Repositories\Auth as AuthRepository;
use Leantime\Domain\Auth\Services\AccessToken;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Auth\Services\AuthUser;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * #4042: a deactivated user's credentials that outlive the login stop working.
 *
 * The web session is covered by AuthenticateSessionTest. This covers the token credentials:
 * `lt_` API keys (x-api-key / jsonRpc guard), personal access tokens on the Bearer fallback in
 * AuthCheck (Auth::getUserByToken) and on the Sanctum guard (AccessToken::findToken).
 */
class DeactivatedUserCredentialsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        session()->flush();
    }

    private function userRow(string $status): array
    {
        return [
            'id' => 7,
            'firstname' => 'Ada',
            'lastname' => '',
            'username' => 'ada@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'profileId' => 0,
            'clientId' => 0,
            'role' => 20,
            'settings' => '',
            'status' => $status,
            'source' => 'api',
            'twoFAEnabled' => false,
            'twoFASecret' => '',
            'createdOn' => '2026-01-01 00:00:00',
            'modified' => '2026-01-01 00:00:00',
        ];
    }

    private function apiServiceWithKeyUser(array $keyUserRow): ApiService
    {
        return new ApiService(
            $this->make(ApiRepository::class, ['getAPIKeyUser' => fn () => $keyUserRow]),
            $this->make(UserRepository::class),
            $this->make(ProjectRepository::class),
            $this->make(MenuRepository::class),
        );
    }

    /**
     * A real Auth service whose token store knows one token (for user 7) and whose user lookup
     * returns $userRow. $lastUsedUpdates counts the token's "last used" writes.
     */
    private function authServiceWithTokenUser(array $userRow, int &$lastUsedUpdates = 0): AuthService
    {
        $tokenRepo = $this->make(AccessTokenRepository::class, [
            'findToken' => fn () => ['id' => 3, 'tokenable_id' => 7, 'expires_at' => null, 'created_at' => null],
            'updateLastUsedAt' => function () use (&$lastUsedUpdates) {
                $lastUsedUpdates++;

                return true;
            },
        ]);

        return new AuthService(
            $this->make(EnvironmentCore::class),
            $this->make(SessionManager::class),
            $this->make(LanguageCore::class),
            $this->make(SettingRepository::class),
            $this->make(AuthRepository::class),
            $this->make(UserRepository::class, ['getUser' => fn () => $userRow]),
            $tokenRepo,
        );
    }

    public function test_active_api_key_authenticates(): void
    {
        $apiUser = $this->apiServiceWithKeyUser($this->userRow('a'))->getAPIKeyUser('lt_ada_secret');

        $this->assertIsArray($apiUser);
        $this->assertSame(7, session('userdata.id'));
    }

    public function test_deactivated_api_key_is_refused(): void
    {
        foreach (['', '0'] as $inactiveStatus) {
            session()->flush();

            $apiUser = $this->apiServiceWithKeyUser($this->userRow($inactiveStatus))->getAPIKeyUser('lt_ada_secret');

            $this->assertFalse($apiUser, "an API key with status '$inactiveStatus' must not authenticate");
            $this->assertNull(session('userdata'), 'no session may be built for a deactivated key');
        }
    }

    public function test_api_key_with_wrong_secret_is_still_refused(): void
    {
        $this->assertFalse($this->apiServiceWithKeyUser($this->userRow('a'))->getAPIKeyUser('lt_ada_wrong'));
    }

    public function test_bearer_token_of_active_user_authenticates(): void
    {
        $lastUsedUpdates = 0;
        $user = $this->authServiceWithTokenUser($this->userRow('A'), $lastUsedUpdates)->getUserByToken('plain-token');

        $this->assertIsArray($user);
        $this->assertSame(7, $user['id']);
        $this->assertSame(1, $lastUsedUpdates);
    }

    public function test_bearer_token_of_deactivated_user_is_refused(): void
    {
        $lastUsedUpdates = 0;
        $user = $this->authServiceWithTokenUser($this->userRow('0'), $lastUsedUpdates)->getUserByToken('plain-token');

        $this->assertFalse($user);
        $this->assertSame(0, $lastUsedUpdates, 'a refused token is not marked as used');
    }

    private function bindSanctumTokenUser(array $userRow): void
    {
        // AccessToken::findToken resolves its token store and its tokenable (AuthUser) from the container.
        $this->app->instance(AccessTokenRepository::class, $this->make(AccessTokenRepository::class, [
            'findToken' => fn () => ['id' => 3, 'tokenable_id' => 7, 'expires_at' => null, 'created_at' => null],
        ]));
        $this->app->instance(AuthUser::class, new AuthUser($this->authServiceWithTokenUser($userRow)));
    }

    public function test_sanctum_token_of_active_user_authenticates(): void
    {
        $this->bindSanctumTokenUser($this->userRow('a'));

        $accessToken = AccessToken::findToken('plain-token');

        $this->assertInstanceOf(AccessToken::class, $accessToken);
        $this->assertSame(7, session('userdata.id'));
    }

    public function test_sanctum_token_of_deactivated_user_is_refused(): void
    {
        $this->bindSanctumTokenUser($this->userRow(''));

        $this->assertFalse(AccessToken::findToken('plain-token'));
        $this->assertNull(session('userdata'), 'no session may be built for a deactivated user');
    }
}
