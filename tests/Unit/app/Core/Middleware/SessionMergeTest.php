<?php

namespace Unit\app\Core\Middleware;

use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Leantime\Core\Middleware\StartSession;
use ReflectionMethod;
use Unit\TestCase;

/**
 * Regression coverage for the optimistic session-concurrency strategy in
 * StartSession. The original blanket-locking existed because a no-lock version
 * lost session writes: two concurrent requests would each overwrite the whole
 * session blob, clobbering each other (e.g. a project switch reverted by a
 * background widget). The merge-on-write strategy must persist ONLY the keys a
 * request actually changed, re-reading the freshest state first, so a concurrent
 * writer's keys survive.
 */
class SessionMergeTest extends TestCase
{
    private function middleware(): StartSession
    {
        return new StartSession(app('session'));
    }

    private function invokeDiff(array $initial, array $current): array
    {
        $method = new ReflectionMethod(StartSession::class, 'diffSession');
        $method->setAccessible(true);

        return $method->invoke($this->middleware(), $initial, $current);
    }

    private function invokeMerge(Store $session, array $changed, array $removed): void
    {
        $method = new ReflectionMethod(StartSession::class, 'mergeSessionChanges');
        $method->setAccessible(true);
        $method->invoke($this->middleware(), $session, $changed, $removed);
    }

    public function test_diff_detects_added_changed_and_removed_keys(): void
    {
        [$changed, $removed] = $this->invokeDiff(
            ['currentProject' => 1, 'keep' => 'same', 'goingAway' => 'x'],
            ['currentProject' => 2, 'keep' => 'same', 'brandNew' => 'y'],
        );

        $this->assertSame(['currentProject' => 2, 'brandNew' => 'y'], $changed);
        $this->assertSame(['goingAway'], $removed);
    }

    public function test_pure_read_produces_no_diff(): void
    {
        [$changed, $removed] = $this->invokeDiff(
            ['currentProject' => 1, 'nested' => ['a' => 1]],
            ['currentProject' => 1, 'nested' => ['a' => 1]],
        );

        $this->assertSame([], $changed);
        $this->assertSame([], $removed);
    }

    /**
     * The core race: request B loads the session, request A switches the project
     * and commits first, then B persists. B only changed `lastPage`, so the merge
     * must keep A's `currentProject = 2` rather than reverting it to the value B
     * originally loaded.
     */
    public function test_merge_preserves_a_concurrent_writers_key(): void
    {
        $handler = new ArraySessionHandler(120);
        $name = 'leantime_session';
        // Store::setId() rejects ids that aren't 40-char alphanumeric and
        // generates a random one instead, so the id must be a valid session id
        // for the three stores to share state through the handler.
        $id = str_repeat('a', 40);

        // Seed the persisted session.
        $seed = new Store($name, $handler, $id);
        $seed->start();
        $seed->put('currentProject', 1);
        $seed->put('userdata.id', 99);
        $seed->save();

        // Request B starts and loads the current state.
        $requestB = new Store($name, $handler, $id);
        $requestB->start();
        $bInitial = $requestB->all();
        $requestB->put('lastPage', '/dashboard/home'); // B's only change

        // Request A switches the project and commits BEFORE B persists.
        $requestA = new Store($name, $handler, $id);
        $requestA->start();
        $requestA->put('currentProject', 2);
        $requestA->save();

        // B persists via the merge strategy (diff of B's change against B's snapshot).
        [$changed, $removed] = $this->invokeDiff($bInitial, $requestB->all());
        $this->invokeMerge($requestB, $changed, $removed);

        // Read the final persisted state.
        $verify = new Store($name, $handler, $id);
        $verify->start();

        $this->assertSame(2, $verify->get('currentProject'), 'concurrent project switch was clobbered');
        $this->assertSame('/dashboard/home', $verify->get('lastPage'), 'B\'s own write was lost');
        $this->assertSame(99, $verify->get('userdata.id'), 'untouched key was dropped');
    }

    public function test_merge_applies_removed_keys(): void
    {
        $handler = new ArraySessionHandler(120);
        $name = 'leantime_session';
        $id = str_repeat('b', 40);

        $seed = new Store($name, $handler, $id);
        $seed->start();
        $seed->put('currentIdeaCanvas', 5);
        $seed->put('currentProject', 3);
        $seed->save();

        $request = new Store($name, $handler, $id);
        $request->start();
        $initial = $request->all();
        $request->forget('currentIdeaCanvas');

        [$changed, $removed] = $this->invokeDiff($initial, $request->all());
        $this->invokeMerge($request, $changed, $removed);

        $verify = new Store($name, $handler, $id);
        $verify->start();

        $this->assertFalse($verify->has('currentIdeaCanvas'), 'removed key should not be persisted');
        $this->assertSame(3, $verify->get('currentProject'));
    }
}
