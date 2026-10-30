<?php

namespace Unit\app\Domain\Auth\Services;

use Leantime\Domain\Auth\Services\SecondFactor;
use PHPUnit\Framework\TestCase;
use RobThree\Auth\TwoFactorAuth;

/**
 * Session-free TOTP verification: the code the library would produce for a
 * secret at a given time verifies at that time; anything else doesn't.
 */
class SecondFactorTest extends TestCase
{
    private const SECRET = 'JBSWY3DPEHPK3PXP';

    public function test_the_current_code_verifies_and_a_stale_one_does_not(): void
    {
        $at = 1_800_000_000;
        $code = (new TwoFactorAuth('Leantime'))->getCode(self::SECRET, $at);

        $this->assertTrue((new SecondFactor)->verify(self::SECRET, $code, $at));
        $this->assertFalse((new SecondFactor)->verify(self::SECRET, $code, $at + 600), 'ten minutes later the code is gone');
    }

    public function test_empty_inputs_never_verify(): void
    {
        $this->assertFalse((new SecondFactor)->verify('', '123456'));
        $this->assertFalse((new SecondFactor)->verify(self::SECRET, ''));
    }
}
