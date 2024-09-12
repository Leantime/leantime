<?php

namespace Leantime\Core\Console;

use Illuminate\Console\Command as LaravelCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Application as ConsoleApplicationContract;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Arr;
use Illuminate\Support\ProcessUtils;
use Illuminate\Support\Str;
use Leantime\Core\Console\Application as LeantimeCli;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\EventDispatcher;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Process\PhpExecutableFinder;

class ConsoleKernel extends Kernel implements ConsoleKernelContract
{
    use DispatchesEvents;

    protected $artisan;

    protected $commandStartedAt;

    protected $bootstrappers = [];

    /**
     * Bootstrap the application for artisan commands.
     *
     * @return void
     */
    public function bootstrap()
    {

        if (! $this->app->hasBeenBootstrapped()) {

            $this->app->bootstrapWith($this->getBootstrappers());
        }

        $this->app->loadDeferredProviders();

        if (! $this->commandsLoaded) {
            $this->commands();

            $this->commandsLoaded = true;
        }

        $this->bindSchedule();

    }

    public function getArtisan(): ConsoleApplicationContract|ConsoleApplication
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

        app()->alias(\Illuminate\Console\Application::class, ConsoleApplicationContract::class);
        app()->alias(\Illuminate\Console\Application::class, ConsoleApplication::class);

        return $this->artisan ??= app()->instance(\Illuminate\Console\Application::class, new class extends ConsoleApplication implements ConsoleApplicationContract
        {
            /**
             * The output from the previous command.
             *
             * @var \Symfony\Component\Console\Output\BufferedOutput
             */
            protected $lastOutput;

            /**
             * Run an Artisan console command by name.
             *
             * @param  string  $command
             * @param  \Symfony\Component\Console\Output\OutputInterface|null  $outputBuffer
             * @return int
             *
             * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
             */
            public function call($command, array $parameters = [], $outputBuffer = null)
            {
                $command = class_exists($command) ? app()->make($command)->getName() : $command;
                array_unshift($parameters, $command);
                $input = new \Symfony\Component\Console\Input\ArrayInput($parameters);

                if (! $this->has($command)) {
                    throw new \Symfony\Component\Console\Exception\CommandNotFoundException(sprintf('The command "%s" does not exist.', $command));
                }

                return $this->run(
                    $input,
                    $this->lastOutput = $outputBuffer ?: new \Symfony\Component\Console\Output\BufferedOutput
                );
            }

            /**
             * Get the output for the last run command.
             *
             * @return string
             */
            public function output()
            {
                return $this->lastOutput && method_exists($this->lastOutput, 'fetch')
                    ? $this->lastOutput->fetch()
                    : '';
            }

            /**
             * Determine the proper PHP executable.
             *
             * @return string
             */
            public static function phpBinary()
            {
                return ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false));
            }

            /**
             * Determine the proper Artisan executable.
             *
             * @return string
             */
            public static function artisanBinary()
            {
                return ProcessUtils::escapeArgument('bin/leantime');
            }
        });
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

        $customCommands = $customPluginCommands = null;

        $ltCommands = collect(glob(APP_ROOT.'/app/Command/*.php') ?? []);

        $commands = collect(Arr::flatten($ltCommands))
            ->map(fn ($path) => $this->getApplication()->getNamespace().Str::of($path)->remove([APP_ROOT.'/app/', APP_ROOT.'/Custom/'])->replace(['/', '.php'], ['\\', ''])->toString());

        collect($commands)
            ->each(function ($command) {

                LeantimeCli::starting(function ($cli) use ($command) {

                    if (
                        ! is_subclass_of($command, SymfonyCommand::class)
                        || (new \ReflectionClass($command))->isAbstract()
                    ) {
                        return;
                    }

                    $command = $this->app->make($command);

                    $cli->add($command);
                });

                /*
                if (
                    ! is_subclass_of($command, SymfonyCommand::class)
                    || (new \ReflectionClass($command))->isAbstract()
                ) {
                    return;
                }
                var_dump($command);
                $command = $this->getArtisan()->make($command);
                var_dump("made");

                if ($command instanceof LaravelCommand) {
                    $command->setLaravel($this->getArtisan());
                }


                $this->getArtisan()->add($command);*/

            });

        $commandsLoaded = true;
    }

    /**
     * Schedule tasks to be executed.
     *
     * @return void
     */
    protected function bindSchedule()
    {
        // Set default timezone
        config(['app.timezone' => config('defaultTimezone')]);
        app()->singleton(Schedule::class, function ($app) {
            $schedule = tap(new Schedule($app['config']['defaultTimezone']))
                ->useCache($app['config']['cache.default']);

            self::dispatch_event('cron', ['schedule' => $schedule], 'schedule');

            return $schedule;
        });
    }

    /**
     * Handle an incoming console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $output
     * @return int
     */
    public function handle($input, $output = null)
    {
        $this->commandStartedAt = microtime(true);

        try {
            $this->bootstrap();

            return $this->getArtisan()->run($input, $output);

        } catch (\Throwable $e) {

            dd($e);
            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
        }
    }

    /**
     * Run an Artisan console command by name.
     *
     * @param  string  $command
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $outputBuffer
     * @return int
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        $this->bootstrap();

        return $this->getArtisan()->call($command, $parameters, $outputBuffer);
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

    /**
     * Get all of the commands registered with the console.
     *
     * @return array
     */
    public function all()
    {
        $this->bootstrap();

        return $this->getArtisan()->all();
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        $this->bootstrap();

        return $this->getArtisan()->output();
    }

    /**
     * Terminate the application.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  int  $status
     * @return void
     */
    public function terminate($input, $status)
    {
        if (method_exists($this->getApplication(), 'terminate')) {
            $this->getApplication()->terminate();
        }

        if (is_null($this->commandStartedAt)) {
            return;
        }

        self::dispatch_event('command', ['input' => $input, 'status' => $status]);

        $this->commandStartedAt = null;
    }

    /**
     * Get the application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication()
    {
        return app();
    }

    public function getBootstrappers(): array
    {

        $bootstrappers = [
            \Leantime\Core\Bootstrap\LoadEnvironmentVariables::class,
            \Leantime\Core\Bootstrap\LoadConfig::class,
            \Leantime\Core\Bootstrap\HandleExceptions::class,
            \Leantime\Core\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ];

        return self::dispatch_filter('http_bootstrappers', $bootstrappers);
    }
}
