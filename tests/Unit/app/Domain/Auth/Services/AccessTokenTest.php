<?php

namespace Unit\app\Domain\Auth\Services;

use Illuminate\Validation\UnauthorizedException;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\RoleCeiling;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Auth\Services\AccessToken;
use Leantime\Domain\Users\Permissions\UsersPermissions;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Personal access tokens: everyone manages their own; managing another user's tokens follows
 * the `users.edit` grant (admin+ by default), not the admin rank, and only for accounts whose
 * role the caller could assign (RoleCeiling).
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

        // User 2 is an editor; the caller's ceiling allows every role except owner.
        $this->app->instance(UserRepository::class, $this->make(UserRepository::class, [
            'getUser' => fn (int $id) => match ($id) {
                2 => ['id' => 2, 'role' => 'editor'],
                3 => ['id' => 3, 'role' => 'owner'],
                default => false,
            },
        ]));
        $this->app->instance(RoleCeiling::class, $this->make(RoleCeiling::class, [
            'canAssign' => fn (mixed $role) => $role !== 'owner',
        ]));
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

    /**
     * users.edit alone is not enough for an account above the caller's ceiling: an admin-level
     * custom role must not mint or revoke an owner's tokens.
     */
    public function test_tokens_of_an_account_above_the_callers_ceiling_are_refused(): void
    {
        $this->grantPermissions([UsersPermissions::EDIT]);
        $repo = $this->make(AccessTokenRepository::class, [
            'createToken' => fn () => $this->fail('no token may be created for an owner account'),
            'findTokenById' => fn () => ['id' => 9, 'tokenable_id' => 3],
            'deleteToken' => fn () => $this->fail('an owner token must not be revoked'),
        ]);

        $this->assertFalse($this->makeAccessToken($repo)->createToken(3, 'ci'));

        $this->expectException(UnauthorizedException::class);
        $this->makeAccessToken($repo)->deleteToken(9);
    }

    public function test_tokens_of_an_unknown_account_are_refused(): void
    {
        $this->grantPermissions([UsersPermissions::EDIT]);
        $repo = $this->make(AccessTokenRepository::class, [
            'createToken' => fn () => $this->fail('no token may be created for a missing account'),
        ]);

        $this->assertFalse($this->makeAccessToken($repo)->createToken(404, 'ci'));
    }
}
