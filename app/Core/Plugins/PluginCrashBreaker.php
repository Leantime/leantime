<?php

namespace Leantime\Core\Plugins;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Disables a plugin that keeps breaking requests.
 *
 * Every error that fails a request is checked for the plugin it came from (a stack frame, or a
 * compiled view, inside app/Plugins/{Plugin} or {Plugin}.phar). When one plugin causes
 * {@see THRESHOLD} failed requests within {@see WINDOW_SECONDS}, it is disabled and the
 * administrators are notified, so a broken plugin hook can't take every page down with it.
 *
 * Only faults count (5xx and plain errors): not-found, validation, permission and other 4xx
 * outcomes are part of normal use.
 */
class PluginCrashBreaker
{
    public const THRESHOLD = 5;

    public const WINDOW_SECONDS = 600;

    /**
     * Records a request-failing error and disables its plugin once it crosses the threshold.
     * Never throws: it runs inside the exception handler.
     */
    public function record(Throwable $error): void
    {
        try {
            if (! $this->counts($error)) {
                return;
            }

            $pluginFolder = $this->pluginFor($error);
            if ($pluginFolder === null) {
                return;
            }

            // Concurrent failing requests would otherwise read the same window and overwrite each
            // other (never reaching the threshold, or disabling twice). Waits briefly for the lock;
            // a failure that can't get it in time is dropped rather than slowing the error page.
            $lock = Cache::lock('plugins.failures.lock.'.strtolower($pluginFolder), 5);
            $lock->block(2, function () use ($pluginFolder, $error) {
                $this->countFailure($pluginFolder, $error);
            });
        } catch (Throwable $breakerError) {
            Log::warning('Plugin crash breaker could not record an error: '.$breakerError->getMessage());
        }
    }

    /**
     * Adds a failure to the plugin's window and disables it at the threshold. Runs under the
     * plugin's lock (see {@see record()}).
     */
    private function countFailure(string $pluginFolder, Throwable $error): void
    {
        $cacheKey = 'plugins.failures.'.strtolower($pluginFolder);
        $now = time();
        $recentFailures = array_values(array_filter(
            (array) Cache::get($cacheKey, []),
            fn ($timestamp) => is_int($timestamp) && $timestamp > $now - self::WINDOW_SECONDS
        ));
        $recentFailures[] = $now;

        if (count($recentFailures) < self::THRESHOLD) {
            Cache::put($cacheKey, $recentFailures, self::WINDOW_SECONDS);

            return;
        }

        Cache::forget($cacheKey);
        app(PluginService::class)->disableAfterFailures($pluginFolder, $error->getMessage());
    }

    /**
     * The folder of the plugin an error came from, or null when no plugin is involved.
     *
     * Checks where the error was thrown, then the stack (innermost first), then the message,
     * which is where Laravel names the source view of an error inside a compiled template.
     */
    public function pluginFor(Throwable $error): ?string
    {
        for ($current = $error; $current !== null; $current = $current->getPrevious()) {
            $files = [$current->getFile()];
            foreach ($current->getTrace() as $frame) {
                if (isset($frame['file'])) {
                    $files[] = $frame['file'];
                }
            }
            $files[] = $current->getMessage();

            foreach ($files as $file) {
                if (preg_match('#/app/Plugins/([A-Za-z0-9_-]+)/#', (string) $file, $match) === 1) {
                    return $match[1];
                }
                if (preg_match('#phar://[^\s]*?/([A-Za-z0-9_-]+)\.phar#', (string) $file, $match) === 1) {
                    return $match[1];
                }
            }
        }

        return null;
    }

    /**
     * Whether an error is a fault (counts) rather than an expected outcome of normal use.
     */
    public function counts(Throwable $error): bool
    {
        // Leantime's own exceptions (not found, forbidden, validation…) carry an HTTP status too.
        if ($error instanceof HttpExceptionInterface) {
            return $error->getStatusCode() >= 500;
        }

        // A response thrown on purpose (an auth redirect, an abort with a page): only a 5xx counts.
        if ($error instanceof HttpResponseException) {
            return $error->getResponse()->getStatusCode() >= 500;
        }

        return ! ($error instanceof LaravelValidationException
            || $error instanceof AuthenticationException
            || $error instanceof TokenMismatchException);
    }
}
