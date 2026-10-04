<?php

namespace Unit\app\Core\Console;

use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Console\ConsoleKernel;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

/**
 * Scheduled jobs all need the database, so before install nothing is scheduled; otherwise a fresh
 * container's cron reports queue:emails, reports:telemetry, ... as FAIL every minute (#3134).
 */
class ConsoleKernelScheduleTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private int $cronEventCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // Priority 1 runs before the domain register.php listeners, which need services this
        // database-less unit environment cannot fully provide.
        EventDispatcher::add_event_listener('leantime.core.console.consolekernel.schedule.cron', function () {
            $this->cronEventCount++;
        }, 1);
    }

    private function runSchedule(bool $installed): void
    {
        app()->instance(SettingRepository::class, $this->makeEmpty(SettingRepository::class, [
            'checkIfInstalled' => $installed,
        ]));

        $kernel = new ConsoleKernel(app(), app('events'));
        (new \ReflectionMethod(ConsoleKernel::class, 'schedule'))->invoke($kernel, new Schedule);
    }

    public function test_nothing_is_scheduled_before_install(): void
    {
        $this->runSchedule(installed: false);

        $this->assertSame(0, $this->cronEventCount);
    }

    public function test_jobs_are_scheduled_once_installed(): void
    {
        $this->runSchedule(installed: true);

        $this->assertSame(1, $this->cronEventCount);
    }
}
