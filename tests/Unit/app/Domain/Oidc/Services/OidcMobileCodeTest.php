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

        $this->assertSame(['userId' => 42, 'challenge' => 'challenge-abc', 'secondFactorFailures' => 0], $first);
        $this->assertSame($first, $second, 'peekCode() must not consume the code');
    }

    // ---------------------------------------------------------------------
    // Second factor: one longer life per code, and a failure count that rides
    // along without touching the deadline.
    // ---------------------------------------------------------------------

    /** The stored payload, straight from the cache (the raw code is hashed into the key). */
    private function stored(string $code): ?array
    {
        $data = \Illuminate\Support\Facades\Cache::get('oidc.mobile.code.'.hash('sha256', $code));

        return is_array($data) ? $data : null;
    }

    public function test_extend_for_second_factor_gives_three_minutes_once(): void
    {
        $code = $this->codes->createCode(42, 'ch');
        $before = $this->stored($code)['expiresAt'];
        $this->assertLessThanOrEqual(time() + 60, $before, 'a fresh code lives 60s');

        $this->assertTrue($this->codes->extendForSecondFactor($code));
        $extended = $this->stored($code)['expiresAt'];
        $this->assertGreaterThanOrEqual(time() + OidcMobileCode::SECOND_FACTOR_TTL_SECONDS - 1, $extended);

        $this->assertFalse($this->codes->extendForSecondFactor($code), 'only once per code');
        $this->assertSame($extended, $this->stored($code)['expiresAt'], 'a second call changes nothing');
        $this->assertNotNull($this->codes->peekCode($code), 'extending never consumes');
    }

    public function test_extend_unknown_code_is_refused(): void
    {
        $this->assertFalse($this->codes->extendForSecondFactor('never-minted'));
    }

    public function test_failures_count_up_and_keep_the_deadline(): void
    {
        $code = $this->codes->createCode(42, 'ch');
        $this->codes->extendForSecondFactor($code);
        $deadline = $this->stored($code)['expiresAt'];

        $this->assertSame(1, $this->codes->recordSecondFactorFailure($code));
        $this->assertSame(2, $this->codes->recordSecondFactorFailure($code));
        $this->assertSame(2, $this->codes->peekCode($code)['secondFactorFailures'], 'the count survives a peek');
        $this->assertSame($deadline, $this->stored($code)['expiresAt'], 'counting never moves the deadline');
        $this->assertSame(0, $this->codes->recordSecondFactorFailure('never-minted'));
    }

    public function test_an_expired_code_cannot_be_extended_or_read(): void
    {
        $code = $this->codes->createCode(42, 'ch');
        $key = 'oidc.mobile.code.'.hash('sha256', $code);
        $data = $this->stored($code);
        $data['expiresAt'] = time() - 1;
        \Illuminate\Support\Facades\Cache::put($key, $data, 60);

        $this->assertNull($this->codes->peekCode($code));
        $this->assertFalse($this->codes->extendForSecondFactor($code));
        $this->assertFalse($this->codes->consumeCode($code), 'a dead code reports nothing to consume');
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
