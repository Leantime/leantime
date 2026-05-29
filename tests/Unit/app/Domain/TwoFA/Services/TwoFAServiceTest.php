<?php

namespace Unit\app\Domain\TwoFA\Services;

use Leantime\Domain\TwoFA\Services\TwoFA;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use RobThree\Auth\TwoFactorAuth;
use Unit\TestCase;

/**
 * Unit tests for the TwoFA service extracted from the TwoFA/Edit controller.
 */
class TwoFAServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_disable_clears_secret_and_flag(): void
    {
        $captured = null;
        $repo = $this->make(UserRepository::class, [
            'patchUser' => function ($id, $params) use (&$captured) {
                $captured = [$id, $params];

                return true;
            },
        ]);

        (new TwoFA($repo))->disable2FA(7);

        $this->assertSame(7, $captured[0]);
        $this->assertSame(0, $captured[1]['twoFAEnabled']);
        $this->assertNull($captured[1]['twoFASecret']);
    }

    public function test_save_secret_persists_without_enabling(): void
    {
        $captured = null;
        $repo = $this->make(UserRepository::class, [
            'patchUser' => function ($id, $params) use (&$captured) {
                $captured = $params;

                return true;
            },
        ]);

        (new TwoFA($repo))->saveSecret(7, 'SECRETBASE32');

        $this->assertSame(['twoFASecret' => 'SECRETBASE32'], $captured);
    }

    public function test_verify_and_enable_rejects_invalid_code(): void
    {
        $persistCalls = 0;
        $repo = $this->make(UserRepository::class, [
            'patchUser' => function () use (&$persistCalls) {
                $persistCalls++;

                return true;
            },
        ]);

        $tfa = new TwoFactorAuth('Leantime', 6, 30, 'sha1');
        $secret = $tfa->createSecret(160);
        $validCode = $tfa->getCode($secret);
        // A numerically-adjacent code is not a valid TOTP code for this secret.
        $wrongCode = str_pad((string) ((((int) $validCode) + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        $result = (new TwoFA($repo))->verifyAndEnable(7, $secret, $wrongCode);

        $this->assertFalse($result);
        $this->assertSame(0, $persistCalls, 'An invalid code must not enable 2FA');
    }

    public function test_verify_and_enable_accepts_valid_code(): void
    {
        $captured = null;
        $repo = $this->make(UserRepository::class, [
            'patchUser' => function ($id, $params) use (&$captured) {
                $captured = $params;

                return true;
            },
        ]);

        $tfa = new TwoFactorAuth('Leantime', 6, 30, 'sha1');
        $secret = $tfa->createSecret(160);
        $validCode = $tfa->getCode($secret);

        $result = (new TwoFA($repo))->verifyAndEnable(7, $secret, $validCode);

        $this->assertTrue($result);
        $this->assertSame(1, $captured['twoFAEnabled']);
        $this->assertSame($secret, $captured['twoFASecret']);
    }

    public function test_get_setup_data_generates_secret_and_qr_when_not_enabled(): void
    {
        $repo = $this->make(UserRepository::class, [
            'getUser' => fn ($id) => ['username' => 'jane@example.com', 'twoFASecret' => '', 'twoFAEnabled' => 0],
        ]);

        $setup = (new TwoFA($repo))->getSetupData(7);

        $this->assertNotEmpty($setup['secret']);
        $this->assertFalse($setup['twoFAEnabled']);
        $this->assertIsString($setup['qrData']);
        $this->assertStringStartsWith('data:image/png', $setup['qrData']);
    }

    public function test_get_setup_data_omits_qr_when_already_enabled(): void
    {
        $repo = $this->make(UserRepository::class, [
            'getUser' => fn ($id) => ['username' => 'jane@example.com', 'twoFASecret' => 'EXISTINGSECRET', 'twoFAEnabled' => 1],
        ]);

        $setup = (new TwoFA($repo))->getSetupData(7);

        $this->assertSame('EXISTINGSECRET', $setup['secret']);
        $this->assertTrue($setup['twoFAEnabled']);
        $this->assertNull($setup['qrData']);
    }
}
