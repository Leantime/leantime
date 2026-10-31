<?php

namespace Unit\app\Domain\Auth\Services;

use Illuminate\Validation\UnauthorizedException;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Auth\Services\AccessToken;
use Leantime\Domain\Users\Permissions\UsersPermissions;
use Unit\TestCase;

/**
 * Personal access tokens: everyone manages their own; managing another user's tokens follows
 * the `users.edit` grant (admin+ by default), not the admin rank.
 */
class AccessTokenTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, string> Permission keys the service asked about. */
    private array $checkedPermissions = [];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata.id' => 1]);
        $this->checkedPermissions = [];
    }

    private function grantPermissions(array $grantedKeys): void
    {
        $this->app->instance(PermissionService::class, $this->make(PermissionService::class, [
            'currentUserCan' => function (string $permission) use ($grantedKeys) {
                $this->checkedPermissions[] = $permission;

                return in_array($permission, $grantedKeys, true);
            },
        ]));
    }

    private function makeAccessToken(AccessTokenRepository $repo): AccessToken
    {
        // The constructor resolves AuthUser (DB-backed); these methods only need the repository.
        $accessToken = (new \ReflectionClass(AccessToken::class))->newInstanceWithoutConstructor();
        $tokenRepo = new \ReflectionProperty(AccessToken::class, 'tokenRepo');
        $tokenRepo->setAccessible(true);
        $tokenRepo->setValue($accessToken, $repo);

        return $accessToken;
    }

    public function test_create_token_for_another_user_follows_users_edit(): void
    {
        $this->grantPermissions([UsersPermissions::EDIT]);
        $repo = $this->make(AccessTokenRepository::class, [
            'createToken' => fn (int $userId, string $name) => ['tokenable_id' => $userId, 'name' => $name],
        ]);

        $token = $this->makeAccessToken($repo)->createToken(2, 'ci');

        $this->assertSame(2, $token->tokenable_id);
        $this->assertSame([UsersPermissions::EDIT], $this->checkedPermissions);
    }

    public function test_create_token_for_another_user_is_refused_without_users_edit(): void
    {
        $this->grantPermissions([]);
        $repo = $this->make(AccessTokenRepository::class, [
            'createToken' => fn () => $this->fail('no token may be created for another user'),
        ]);

        $this->assertFalse($this->makeAccessToken($repo)->createToken(2, 'ci'));
    }

    public function test_create_own_token_needs_no_permission(): void
    {
        $this->grantPermissions([]);
        $repo = $this->make(AccessTokenRepository::class, [
            'createToken' => fn (int $userId) => ['tokenable_id' => $userId],
        ]);

        $this->assertSame(1, $this->makeAccessToken($repo)->createToken(1)->tokenable_id);
    }

    public function test_listing_another_users_tokens_follows_users_edit(): void
    {
        $repo = $this->make(AccessTokenRepository::class, [
            'getAllTokensByUserId' => fn () => [['id' => 5]],
        ]);

        $this->grantPermissions([UsersPermissions::EDIT]);
        $this->assertSame([['id' => 5]], $this->makeAccessToken($repo)->getUserTokens(2));

        $this->grantPermissions([]);
        $this->expectException(UnauthorizedException::class);
        $this->makeAccessToken($repo)->getUserTokens(2);
    }

    public function test_deleting_another_users_token_follows_users_edit(): void
    {
        $deleted = [];
        $repo = $this->make(AccessTokenRepository::class, [
            'findTokenById' => fn () => ['id' => 5, 'tokenable_id' => 2],
            'deleteToken' => function (int $id) use (&$deleted) {
                $deleted[] = $id;

                return true;
            },
        ]);

        $this->grantPermissions([UsersPermissions::EDIT]);
        $this->assertTrue($this->makeAccessToken($repo)->deleteToken(5));
        $this->assertSame([5], $deleted);

        $this->grantPermissions([]);
        $this->expectException(UnauthorizedException::class);
        $this->makeAccessToken($repo)->deleteToken(5);
    }
}
