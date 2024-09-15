<?php

namespace Leantime\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Leantime\Core\Console\Application as LeantimeCli;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\EventDispatcher;

class ConsoleKernel extends Kernel implements ConsoleKernelContract
{
    use DispatchesEvents;

    protected $app;

    protected $artisan;

    protected $commandStartedAt;

    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Leantime\Core\Bootstrap\LoadConfig::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Leantime\Core\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * Schedule tasks to be executed.
     *
     * @return void
     */
    /*protected function defineConsoleSchedule()
    {
        // Set default timezone
        $this->app['config']->set(['app.timezone' => $this->app['config']->get('defaultTimezone')]);
        $this->app->singleton(Schedule::class, function ($app) {
            $schedule = tap(new Schedule($app['config']['defaultTimezone']))
                ->useCache($app['config']['cache.default']);

            self::dispatchEvent('cron', ['schedule' => $schedule], 'schedule');

            return $schedule;
        });
    }*/

    /**
     * Handle an incoming console command.
     *
     * @return int
     */
    public function handle($input, $output = null)
    {

        $this->commandStartedAt = Carbon::now();

        try {

            if (in_array($input->getFirstArgument(), ['env:encrypt', 'env:decrypt'], true)) {
                $this->bootstrapWithoutBootingProviders();
            }

            $this->bootstrap();
            $cli = $this->getArtisan();
            $output = $cli->run($input, $output);

            return $output;

        } catch (\Throwable $e) {

            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
        }
    }

    /**
     * Load and register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        static $commandsLoaded;
        $commandsLoaded ??= false;

        if ($commandsLoaded) {
            return;
        }

        //$customCommands = $customPluginCommands = null;

        //$ltCommands = collect(glob(APP_ROOT.'/app/Command/*.php') ?? []);

        /*$commands = collect(Arr::flatten($ltCommands))
            ->map(fn ($path) => $this->getApplication()->getNamespace().Str::of($path)->remove([APP_ROOT.'/app/', APP_ROOT.'/Custom/'])->replace(['/', '.php'], ['\\', ''])->toString());
        */

        $this->load(APP_ROOT.'/app/Command/');

        $commandsLoaded = true;
    }

    /**
     * Queue an Artisan console command by name.
     *
     * @param  string  $command
     *
     * @todo Implement
     */
    public function queue($command, array $parameters = []): \Illuminate\Foundation\Bus\PendingDispatch
    {
        /** @phpstan-ignore-next-line */
        return \Illuminate\Foundation\Bus\PendingDispatch();
    }

    public function bootstrap()
    {

        if (! $this->app->hasBeenBootstrapped()) {

            $this->app->bootstrapWith($this->bootstrappers());
        }

        $this->app->loadDeferredProviders();

        if (! $this->commandsLoaded) {
            $this->commands();

            $this->commandsLoaded = true;
        }

        //$this->bindSchedule();

    }

    public function getArtisan()
    {

        if (is_null($this->artisan)) {
            $this->artisan = (new LeantimeCli($this->app, $this->events, $this->app->version()))
                ->resolveCommands($this->commands)
                ->setContainerCommandLoader();

            if ($this->symfonyDispatcher instanceof EventDispatcher) {
                $this->artisan->setDispatcher($this->symfonyDispatcher);
                $this->artisan->setSignalsToDispatchEvent();
            }
        }

        return $this->artisan;
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }

    protected function schedule(Schedule $schedule)
    {
        //
    }
}
