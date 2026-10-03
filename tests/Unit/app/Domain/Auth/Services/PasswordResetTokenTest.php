<?php

namespace Unit\app\Domain\Auth\Services;

use Illuminate\Session\SessionManager;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Auth\Repositories\Auth as AuthRepository;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Password reset tokens must be unguessable and must never be stored in a usable form:
 * the emailed token comes from the CSPRNG and only its sha256 hash reaches the database,
 * so every lookup (validate + change) hashes the incoming token first.
 */
class PasswordResetTokenTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function makeService(AuthRepository $authRepo, ?UserRepository $userRepo = null): AuthService
    {
        return new AuthService(
            $this->make(EnvironmentCore::class),
            $this->make(SessionManager::class),
            $this->make(LanguageCore::class, ['__' => fn (string $index) => '%s']),
            $this->make(SettingRepository::class),
            $authRepo,
            $userRepo ?? $this->make(UserRepository::class),
            $this->make(AccessTokenRepository::class),
        );
    }

    public function test_reset_email_carries_random_token_and_only_its_hash_is_stored(): void
    {
        $storedHashes = [];
        $authRepo = $this->make(AuthRepository::class, [
            'setPWResetLink' => function (string $username, string $resetLink) use (&$storedHashes) {
                $storedHashes[] = $resetLink;

                return true;
            },
        ]);
        $userRepo = $this->make(UserRepository::class, [
            'getUserByEmail' => fn () => ['id' => 3, 'username' => 'jane@example.com', 'pwResetCount' => 0],
        ]);

        $sentHtml = [];
        $mailer = $this->make(MailerCore::class, [
            'setContext' => fn () => null,
            'setSubject' => fn () => null,
            'setHtml' => function ($html) use (&$sentHtml) {
                $sentHtml[] = $html;
            },
            'sendMail' => fn () => true,
        ]);
        app()->instance(MailerCore::class, $mailer);

        $service = $this->makeService($authRepo, $userRepo);

        $this->assertTrue($service->generateLinkAndSendEmail('jane@example.com'));
        $this->assertTrue($service->generateLinkAndSendEmail('jane@example.com'));

        $tokens = array_map(function (string $html) {
            $this->assertMatchesRegularExpression('#/auth/resetPw/([0-9a-f]{64})$#', $html);
            preg_match('#/auth/resetPw/([0-9a-f]{64})$#', $html, $match);

            return $match[1];
        }, $sentHtml);

        $this->assertNotSame($tokens[0], $tokens[1], 'each reset request mints a fresh token');

        foreach ($tokens as $i => $token) {
            $this->assertSame(hash('sha256', $token), $storedHashes[$i], 'the database only ever sees the hash');
            $this->assertNotSame($token, $storedHashes[$i]);
        }
    }

    public function test_validate_and_change_look_up_the_hash_of_the_token(): void
    {
        $lookedUp = [];
        $authRepo = $this->make(AuthRepository::class, [
            'validateResetLink' => function (string $hash) use (&$lookedUp) {
                $lookedUp[] = $hash;

                return true;
            },
            'changePW' => function (string $password, string $hash) use (&$lookedUp) {
                $lookedUp[] = $hash;

                return true;
            },
        ]);

        $service = $this->makeService($authRepo);

        $this->assertTrue($service->validateResetLink('plain-token'));
        $this->assertTrue($service->changePw('StrongPass1!', 'plain-token'));

        $this->assertSame([hash('sha256', 'plain-token'), hash('sha256', 'plain-token')], $lookedUp);
    }

    public function test_empty_token_never_reaches_the_repository(): void
    {
        $authRepo = $this->make(AuthRepository::class, [
            'validateResetLink' => function () {
                $this->fail('empty token must not be looked up');
            },
            'changePW' => function () {
                $this->fail('empty token must not be looked up');
            },
        ]);

        $service = $this->makeService($authRepo);

        $this->assertFalse($service->validateResetLink(''));
        $this->assertFalse($service->changePw('StrongPass1!', ''));
    }
}
