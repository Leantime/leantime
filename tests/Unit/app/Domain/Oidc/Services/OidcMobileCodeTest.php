<?php

namespace Tests\Unit\app\Domain\Oidc\Services;

use Leantime\Domain\Oidc\Services\OidcMobileCode;

/**
 * Unit tests for the mobile SSO one-time-code store.
 *
 * Pins the single-use contract: peekCode() is non-destructive, and consumeCode()
 * burns the code exactly once (returns true for the caller that burns it, false
 * for a second/unknown code). Runs against the array cache store from
 * \Unit\TestCase, which supports the atomic lock consumeCode() takes.
 */
class OidcMobileCodeTest extends \Unit\TestCase
{
    private OidcMobileCode $codes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codes = new OidcMobileCode;
    }

    public function test_peek_is_non_destructive_and_returns_the_payload(): void
    {
        $code = $this->codes->createCode(42, 'challenge-abc');

        $first = $this->codes->peekCode($code);
        $second = $this->codes->peekCode($code);

        $this->assertSame(['userId' => 42, 'challenge' => 'challenge-abc'], $first);
        $this->assertSame($first, $second, 'peekCode() must not consume the code');
    }

    public function test_consume_returns_true_once_then_false(): void
    {
        $code = $this->codes->createCode(7, 'ch');

        $this->assertTrue($this->codes->consumeCode($code), 'first consume burns the code');
        $this->assertFalse($this->codes->consumeCode($code), 'a single-use code cannot be consumed twice');
    }

    public function test_consumed_code_no_longer_peeks(): void
    {
        $code = $this->codes->createCode(5, 'ch');
        $this->codes->consumeCode($code);

        $this->assertNull($this->codes->peekCode($code), 'a burned code is gone');
    }

    public function test_consume_unknown_code_returns_false(): void
    {
        $this->assertFalse($this->codes->consumeCode('never-minted'));
    }

    public function test_peek_unknown_code_returns_null(): void
    {
        $this->assertNull($this->codes->peekCode('never-minted'));
    }
}
