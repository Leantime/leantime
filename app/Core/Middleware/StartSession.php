<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Session\Session;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class StartSession
{
    use DispatchesEvents;

    /**
     * The session manager.
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $manager;

    /**
     * The callback that can resolve an instance of the cache factory.
     *
     * @var callable|null
     */
    protected $cacheFactoryResolver;

    /**
     * Create a new session middleware.
     *
     * @return void
     */
    public function __construct(SessionManager $manager, ?callable $cacheFactoryResolver = null)
    {
        $this->manager = $manager;
        $this->cacheFactoryResolver = $cacheFactoryResolver;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(IncomingRequest $request, Closure $next)
    {

        if (! $this->sessionConfigured()) {
            return $next($request);
        }

        // For API and cron requests, use in-memory array driver to prevent
        // persistent session accumulation. Must run BEFORE getSession() so the
        // session object is created with the array handler from the start.
        // Browser AJAX requests (JS calling JSON-RPC) are excluded so they
        // continue to share the user's web session.
        if ($request->isApiOrCronRequest() && ! $request->ajax()) {
            config(['session.driver' => 'array']);
            $this->manager->setDefaultDriver('array');
        }

        $session = $this->getSession($request);

        self::dispatchEvent('session_initialized');

        // API and cron requests are stateful but non-persisting and never lock
        // (unchanged behavior: their writes were never saved to begin with).
        if (! $this->shouldPersistSession($request)) {
            return $this->handleStatelessRequest($request, $session, $next);
        }

        // Web requests use optimistic concurrency: run lock-free, then persist only
        // the keys that actually changed, merging them under a brief lock so parallel
        // requests (e.g. dashboard widgets) can't clobber each other's writes.
        return $this->handleOptimisticRequest($request, $session, $next);

    }

    /**
     * Handle a stateful but non-persisting request (API / cron). The session is
     * started so it can be read, but it is never locked and never written back —
     * this preserves the pre-existing behavior for these request types.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return mixed
     */
    protected function handleStatelessRequest(IncomingRequest $request, $session, Closure $next)
    {
        $request->setLaravelSession($this->startSession($request, $session));

        self::dispatchEvent('session_started');

        $this->collectGarbage($session);

        $response = $next($request);

        $this->addCookieToResponse($response, $session);

        return $response;
    }

    /**
     * Handle a web request with optimistic session concurrency. The request runs
     * without holding the session lock so concurrent requests (e.g. parallel
     * dashboard widgets) are not serialized. Only when the session actually
     * changed do we briefly lock, re-read the freshest persisted state, and merge
     * just this request's changed keys — preventing the lost-update race that
     * previously forced blanket locking.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return mixed
     */
    protected function handleOptimisticRequest(IncomingRequest $request, $session, Closure $next)
    {
        $startTime = microtime(true);

        $request->setLaravelSession($this->startSession($request, $session));

        self::dispatchEvent('session_started');

        $this->collectGarbage($session);

        $initialId = $session->getId();
        $initialData = $session->all();

        $response = $next($request);

        $this->storeCurrentUrl($request, $session);

        $this->persistSessionChanges($request, $session, $initialId, $initialData);

        $duration = microtime(true) - $startTime;
        if ($duration > 3.0) {
            Log::warning("Long session operation detected: {$duration}s for session {$session->getId()}");
        }

        $this->addCookieToResponse($response, $session);

        return $response;
    }

    /**
     * Persist session changes using a lock-on-write strategy.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     */
    protected function persistSessionChanges(IncomingRequest $request, $session, string $initialId, array $initialData): void
    {
        // The session identity changed (login/logout regenerate or invalidate).
        // Keys can't be safely merged onto a different id, so fall back to a full,
        // locked save of the live session.
        if ($session->getId() !== $initialId) {
            $this->withSessionLock($request, $session, fn () => $session->save());

            return;
        }

        [$changed, $removed] = $this->diffSession($initialData, $session->all());

        // Pure read: nothing changed, so we never touch the lock or storage.
        if ($changed === [] && $removed === []) {
            return;
        }

        $this->withSessionLock($request, $session, fn () => $this->mergeSessionChanges($session, $changed, $removed));
    }

    /**
     * Re-read the freshest persisted session state and apply ONLY the keys this
     * request changed/removed onto it, then write it back. Merging by changed-key
     * (rather than overwriting the whole blob) is what lets a concurrent writer's
     * keys survive. Must be called while holding the per-session lock.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @param  array<string, mixed>  $changed
     * @param  array<int, string>  $removed
     */
    protected function mergeSessionChanges($session, array $changed, array $removed): void
    {
        $merged = new Store(
            $session->getName(),
            $session->getHandler(),
            $session->getId(),
            $this->manager->getSessionConfig()['serialization'] ?? 'php'
        );

        $merged->start();

        // Keep the CSRF token consistent with what the live session (and the
        // already-rendered response) used; a fresh Store would otherwise
        // regenerate a different token and break the next POST.
        $merged->put('_token', $session->token());

        foreach ($changed as $key => $value) {
            $merged->put($key, $value);
        }

        foreach ($removed as $key) {
            $merged->forget($key);
        }

        $merged->save();
    }

    /**
     * Compute the keys this request added/changed and the keys it removed,
     * comparing the session state captured before the request against the
     * state after it.
     *
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    protected function diffSession(array $initial, array $current): array
    {
        $changed = [];

        foreach ($current as $key => $value) {
            if (! array_key_exists($key, $initial) || $initial[$key] !== $value) {
                $changed[$key] = $value;
            }
        }

        $removed = array_keys(array_diff_key($initial, $current));

        return [$changed, $removed];
    }

    /**
     * Acquire the per-session lock, run the persistence callback, and release.
     * Falls back to an exponential-backoff retry if the lock can't be acquired.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     */
    protected function withSessionLock(IncomingRequest $request, $session, Closure $callback): void
    {
        // Dynamic lock period for different request types
        $holdLockFor = $this->calculateLockDuration($request); // Hold lock for x seconds after acquiring

        // Maximum time to wait for acquiring the lock if already held
        $maxWaitForLock = 5; // Wait for up to y seconds to acquire the lock

        $lock = $this->cache($this->manager->blockDriver())
            ->lock('session:'.$session->getId(), $holdLockFor)
            ->betweenBlockedAttemptsSleepFor(50);

        try {
            $lock->block($maxWaitForLock);

            $callback();
        } catch (LockTimeoutException $e) {
            Log::warning("Session lock timeout for session {$session->getId()}: {$e->getMessage()}");

            // Implement exponential backoff retry
            $this->retryWithBackoff($callback, $session);
        } finally {
            $lock?->release();
        }
    }

    /**
     * Calculate appropriate lock duration based on request type. This is v0. We'll need to make this smarter
     */
    protected function calculateLockDuration(IncomingRequest $request): int
    {
        if ($request->isMethod('GET')) {
            return 1; // Shorter duration for GET requests
        }

        if ($request->ajax()) {
            return 2; // Medium duration for AJAX requests
        }

        return 3; // Default duration for other requests
    }

    /**
     * Implement exponential backoff retry strategy for the persistence callback.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     */
    protected function retryWithBackoff(Closure $callback, $session, int $attempts = 3): void
    {
        for ($i = 0; $i < $attempts; $i++) {
            try {
                $waitTime = min(100 * pow(2, $i), 1000); // Exponential backoff with max 1 second
                $jitter = random_int(-100, 100); // Add jitter to prevent thundering herd
                usleep(($waitTime + $jitter) * 1000); // Convert to microseconds

                $callback();

                return;
            } catch (\Exception $e) {
                Log::warning("Retry attempt {$i} failed for session {$session->getId()}: {$e->getMessage()}");

                continue;
            }
        }

        // If all retries fail, persist without the lock as a last resort.
        Log::error("All retry attempts failed for session {$session->getId()}, persisting without lock");

        $callback();
    }

    /**
     * Start the session for the given request.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return \Illuminate\Contracts\Session\Session
     */
    protected function startSession(IncomingRequest $request, $session)
    {
        return tap($session, function ($session) use ($request) {
            $session->setRequestOnHandler($request);

            $session->start();
        });
    }

    /**
     * Get the session implementation from the manager.
     *
     * @return \Illuminate\Contracts\Session\Session
     */
    public function getSession(IncomingRequest $request)
    {
        return tap($this->manager->driver(), function ($session) use ($request) {
            $session->setId($request->cookies->get($session->getName()));
        });
    }

    /**
     * Remove the garbage from the session if necessary.
     *
     * @return void
     */
    protected function collectGarbage(Session $session)
    {
        $config = $this->manager->getSessionConfig();

        // Here we will see if this request hits the garbage collection lottery by hitting
        // the odds needed to perform garbage collection on any given request. If we do
        // hit it, we'll call this handler to let it delete all the expired sessions.
        if ($this->configHitsLottery($config)) {
            $session->getHandler()->gc($this->getSessionLifetimeInSeconds());
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @return bool
     */
    protected function configHitsLottery(array $config)
    {
        return random_int(1, $config['lottery'][1]) <= $config['lottery'][0];
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return void
     */
    protected function storeCurrentUrl(IncomingRequest $request, $session)
    {
        // Only full-page navigations set the "previous URL" used for back-redirects.
        // HTMX partials must not, otherwise every background widget load would dirty
        // the session and force a needless lock-merge-save.
        if (
            $request->isMethod('GET')
            && ! $request->isHtmxRequest()
            && $this->shouldPersistSession($request)
        ) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }

    /**
     * Add the session cookie to the application response.
     *
     * @return void
     */
    protected function addCookieToResponse(Response $response, Session $session)
    {
        if ($this->sessionIsPersistent($config = $this->manager->getSessionConfig())) {
            $response->headers->setCookie(new Cookie(
                $session->getName(),
                $session->getId(),
                $this->getCookieExpirationDate(),
                $config['path'],
                $config['domain'],
                $config['secure'] ?? false,
                $config['http_only'] ?? true,
                false,
                $config['same_site'] ?? null,
                $config['partitioned'] ?? false
            ));
        }
    }

    /**
     * Determine whether this request should persist its session to storage.
     * API and cron requests are stateful-but-throwaway and are never persisted.
     *
     * @return bool
     */
    protected function shouldPersistSession(IncomingRequest $request)
    {
        return $request->isApiOrCronRequest() === false && $this->sessionConfigured();
    }

    /**
     * Get the session lifetime in seconds.
     *
     * @return int
     */
    protected function getSessionLifetimeInSeconds()
    {
        return ($this->manager->getSessionConfig()['lifetime'] ?? null) * 60;
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @return \DateTimeInterface|int
     */
    protected function getCookieExpirationDate()
    {
        $config = $this->manager->getSessionConfig();

        return $config['expire_on_close'] ? 0 : Date::instance(
            Carbon::now()->addRealMinutes($config['lifetime'])
        );
    }

    /**
     * Determine if a session driver has been configured.
     *
     * @return bool
     */
    protected function sessionConfigured()
    {
        return ! is_null($this->manager->getSessionConfig()['driver'] ?? null);
    }

    /**
     * Determine if the configured session driver is persistent.
     *
     * @return bool
     */
    protected function sessionIsPersistent(?array $config = null)
    {
        $config = $config ?: $this->manager->getSessionConfig();

        return ! is_null($config['driver'] ?? null);
    }

    /**
     * Resolve the given cache driver.
     *
     * @param  string  $driver
     * @return \Illuminate\Cache\Store
     */
    protected function cache($driver)
    {
        return Cache::store($driver);
    }
}
