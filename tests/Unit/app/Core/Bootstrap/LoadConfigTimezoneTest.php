<?php

namespace Unit\app\Core\Bootstrap;

use Leantime\Core\Bootstrap\LoadConfig;
use Unit\TestCase;

/**
 * Boot-time guarantee behind every DB write: the PHP process runs in UTC, while app.timezone
 * (LEAN_DEFAULT_TIMEZONE) keeps its meaning as the default USER timezone. The bootstrapper used
 * to apply app.timezone to the process, so cron, queue and logged-out requests wrote local time
 * (America/Los_Angeles by default) into UTC columns.
 */
class LoadConfigTimezoneTest extends TestCase
{
    public function test_boot_pins_the_process_to_utc_and_keeps_the_default_user_timezone(): void
    {
        $originalTimezone = date_default_timezone_get();
        $originalEnv = getenv('LEAN_DEFAULT_TIMEZONE');

        try {
            date_default_timezone_set('Asia/Tokyo');
            putenv('LEAN_DEFAULT_TIMEZONE=America/Los_Angeles');
            $_ENV['LEAN_DEFAULT_TIMEZONE'] = 'America/Los_Angeles';

            (new LoadConfig)->bootstrap($this->app);

            $this->assertSame('UTC', date_default_timezone_get(), 'the process must run in UTC');
            $this->assertSame('America/Los_Angeles', config('app.timezone'), 'app.timezone stays the default user timezone');
        } finally {
            date_default_timezone_set($originalTimezone);
            $originalEnv === false ? putenv('LEAN_DEFAULT_TIMEZONE') : putenv('LEAN_DEFAULT_TIMEZONE='.$originalEnv);
            unset($_ENV['LEAN_DEFAULT_TIMEZONE']);
        }
    }
}
