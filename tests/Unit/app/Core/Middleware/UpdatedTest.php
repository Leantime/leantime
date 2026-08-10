<?php

namespace Unit\app\Core\Middleware;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Middleware\Updated;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

/**
 * Guards the stale-session redirect loop: the Updated middleware caches the
 * db-version in the session and (before the fix) never re-read the database
 * once a value was cached. After an admin ran an update in THEIR session,
 * every other live session kept the old cached version, concluded "not
 * updated", and bounced between every page and /install/update until the
 * user's cookies were cleared.
 *
 * The fix self-heals: when a CACHED value would trigger the redirect, the
 * middleware re-reads the real version from the database first — one extra
 * query, only on the would-redirect path.
 */
class UpdatedTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function appSettings(string $codeVersion): void
    {
        $settings = new AppSettings;
        $settings->dbVersion = $codeVersion;
        app()->instance(AppSettings::class, $settings);
    }

    /** @param  array<int, string|false>  $dbVersions  consecutive getSetting('db-version') results */
    private function settingRepo(array $dbVersions, ?int &$reads = null): void
    {
        $reads = 0;
        app()->instance(SettingRepository::class, $this->make(SettingRepository::class, [
            'getSetting' => function () use (&$reads, $dbVersions) {
                $value = $dbVersions[min($reads, count($dbVersions) - 1)];
                $reads++;

                return $value;
            },
        ]));
    }

    /** Run the middleware; returns [response, nextWasCalled]. */
    private function handleRequest(): array
    {
        // The redirect path resolves Frontcontroller from the container; its
        // real constructor needs the full HTTP stack, so bind a bare instance
        // (its redirect()/getCurrentRoute() members are static and work as-is).
        app()->instance(
            \Leantime\Core\Controller\Frontcontroller::class,
            $this->make(\Leantime\Core\Controller\Frontcontroller::class)
        );

        $called = false;
        $response = (new Updated)->handle(
            IncomingRequest::create('/dashboard/home', 'GET'),
            function () use (&$called) {
                $called = true;

                return new \Symfony\Component\HttpFoundation\Response('ok');
            }
        );

        return [$response, $called];
    }

    public function test_stale_session_cache_self_heals_after_an_update_ran_elsewhere(): void
    {
        // Session still remembers 3.5.25 from before the admin upgraded; the
        // DATABASE already says 3.5.26 (matching the code). The middleware
        // must re-read and pass through — not redirect-loop the user.
        session(['dbVersion' => '3.5.25', 'isUpdated' => false]);
        $this->appSettings('3.5.26');
        $this->settingRepo(['3.5.26'], $reads);

        [, $nextCalled] = $this->handleRequest();

        $this->assertTrue($nextCalled, 'a session whose cache is stale but whose DB is current must pass through');
        $this->assertSame(1, $reads, 'the DB is consulted exactly once to heal the cache');
        $this->assertSame('3.5.26', session('dbVersion'), 'the healed version is re-cached');
        $this->assertTrue(session('isUpdated'));
    }

    public function test_current_session_cache_passes_through_without_touching_the_db(): void
    {
        session(['dbVersion' => '3.5.26', 'isUpdated' => true]);
        $this->appSettings('3.5.26');
        $this->settingRepo(['3.5.26'], $reads);

        [, $nextCalled] = $this->handleRequest();

        $this->assertTrue($nextCalled);
        $this->assertSame(0, $reads, 'an up-to-date cached version costs zero settings reads');
    }

    public function test_genuinely_outdated_install_still_redirects_to_update(): void
    {
        // Both the cache AND the database are behind the code: the redirect is
        // correct. The self-heal costs one confirming read, then redirects.
        session(['dbVersion' => '3.5.25', 'isUpdated' => false]);
        $this->appSettings('3.5.26');
        $this->settingRepo(['3.5.25'], $reads);

        [$response, $nextCalled] = $this->handleRequest();

        $this->assertFalse($nextCalled, 'a genuinely outdated install must not pass through');
        $this->assertSame(1, $reads);
        $this->assertStringContainsString('/install/update', $response->headers->get('Location') ?? '', 'the redirect still points at the updater');
        $this->assertFalse(session('isUpdated'));
    }
}
