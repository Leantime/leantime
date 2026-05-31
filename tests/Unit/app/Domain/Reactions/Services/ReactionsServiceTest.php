<?php

namespace Unit\app\Domain\Reactions\Services;

use Leantime\Domain\Reactions\Repositories\Reactions as ReactionsRepository;
use Leantime\Domain\Reactions\Services\Reactions;
use Unit\TestCase;

/**
 * Unit tests for the session-based JSON-RPC wrappers added to the Reactions
 * service (react/unreact): they must derive the user from the session so a
 * caller cannot react as another user.
 */
class ReactionsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_react_uses_the_session_user(): void
    {
        session(['userdata' => ['id' => 42]]);

        $capturedUserId = null;
        $repo = $this->make(ReactionsRepository::class, [
            'getUserReactions' => fn (...$args) => [],
            'addReaction' => function ($userId, ...$rest) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return true;
            },
        ]);

        $result = (new Reactions($repo))->react('tickets', 5, 'thumbsup');

        $this->assertTrue($result);
        $this->assertSame(42, $capturedUserId, 'react() must persist the session user, not a passed id');
    }

    public function test_unreact_uses_the_session_user(): void
    {
        session(['userdata' => ['id' => 7]]);

        $capturedUserId = null;
        $repo = $this->make(ReactionsRepository::class, [
            'removeUserReaction' => function ($userId, ...$rest) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return true;
            },
        ]);

        $result = (new Reactions($repo))->unreact('tickets', 5, 'thumbsup');

        $this->assertTrue($result);
        $this->assertSame(7, $capturedUserId, 'unreact() must remove for the session user, not a passed id');
    }
}
