<?php

namespace Leantime\Domain\Cron\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Console\ConsoleKernel;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Audit\Repositories\Audit;
use Leantime\Domain\Queue\Services\Queue;
use Leantime\Domain\Reports\Services\Reports;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @api
 */
class Cron
{
    use DispatchesEvents;

    private Audit $auditRepo;

    private Queue $queueSvc;

    private Environment $environment;

    private Reports $reportService;

    private int $cronExecTimer = 60;

    public function __construct(Audit $auditRepo, Queue $queueSvc, Environment $environment, Reports $reportService)
    {
        $this->auditRepo = $auditRepo;
        $this->queueSvc = $queueSvc;
        $this->environment = $environment;
        $this->reportService = $reportService;
    }

    /**
     * Runs the scheduled tasks (poor man's cron) after the HTTP response has been sent.
     *
     * Encapsulates the deferred-execution orchestration: it disables the user-abort and
     * execution-time limits, runs Laravel's `schedule:run` command through the console kernel,
     * and registers a shutdown function that logs the scheduler output when debug mode is enabled.
     *
     * @return int The exit code returned by the `schedule:run` command.
     *
     * @api
     */
    public function runScheduledTasks(): int
    {
        ignore_user_abort(true);

        set_time_limit(0);

        $output = new BufferedOutput;
        $consoleKernel = app()->make(ConsoleKernel::class);
        $result = $consoleKernel->call('schedule:run', [], $output);

        register_shutdown_function(function () use ($output) {
            if ($this->environment->debug) {
                Log::info('Cron Schedule Output: '.$output->fetch());
            }
        });

        return $result;
    }
}
