<?php

namespace Leantime\Core\Support;

use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

/**
 * Answers "is Leantime installed?" for both web requests and console runs.
 *
 * Web requests get the answer from the session flag that the Installed middleware sets on every
 * request. Console runs never pass through that middleware, so the flag is never set there; they
 * ask the settings repository instead, which checks the database (and caches a positive answer).
 * If even that check cannot run (no usable database configuration yet), it counts as not installed.
 */
class Installation
{
    /**
     * Whether Leantime is installed.
     */
    public static function isInstalled(): bool
    {
        if (! app()->runningInConsole()) {
            return (bool) session('isInstalled');
        }

        try {
            return app()->make(SettingRepository::class)->checkIfInstalled();
        } catch (\Throwable $e) {
            // No usable database configuration yet (e.g. before install): treat as not installed.
            return false;
        }
    }
}
