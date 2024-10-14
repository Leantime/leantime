<?php

namespace Leantime\Core\Console;

use Illuminate\Console\Command as LaravelCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Application as ConsoleApplicationContract;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Foundation\Bus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ProcessUtils;
use Illuminate\Support\Str;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Plugins\Services\Plugins as PluginsService;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Process\PhpExecutableFinder;

class ConsoleKernel implements ConsoleKernelContract
{
    use DispatchesEvents;

    protected ConsoleApplication $artisan;

    protected $commandStartedAt;

    /**
     * Bootstrap the application for artisan commands.
     *
     * @return void
     */
    public function bootstrap()
    {
        $this->getArtisan();
        $this->commands();
        $this->schedule();
    }

    public function getArtisan(): ConsoleApplicationContract|ConsoleApplication
    {
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
             * @param  string                                                 $command
             * @param  array                                                  $parameters
             * @param  \Symfony\Component\Console\Output\OutputInterface|null $outputBuffer
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
                    $this->lastOutput = $outputBuffer ?: new \Symfony\Component\Console\Output\BufferedOutput()
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
                return ProcessUtils::escapeArgument((new PhpExecutableFinder())->find(false));
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


        Cache::store('installation')->set("commands.core", collect(glob(APP_ROOT . '/app/Command/*.php') ?? [])
            ->filter(function ($command) use (&$customCommands) {
                return ! Arr::has(
                    $customCommands ??= collect(glob(APP_ROOT . '/custom/Command/*.php') ?? []),
                    str_replace(APP_ROOT . '/app', APP_ROOT . '/custom', $command)
                );
            })
            ->concat($customCommands ?? []));

        Cache::store('installation')->set("commands.plugins", collect(glob(APP_ROOT . '/app/Plugins/*/Command/*.php') ?? [])
            ->filter(function ($command) use (&$customPluginCommands) {
                return ! in_array(
                    str_replace(APP_ROOT . '/app', APP_ROOT . '/custom', $command),
                    $customPluginCommands ??= collect(glob(APP_ROOT . '/custom/Plugins/*/Command/*.php') ?? [])->toArray(),
                );
            })
            ->concat($customPluginCommands ?? [])
            // filter by active plugins
            ->filter(fn ($command) => in_array(
                Str::of($command)->after('Plugins/')->before('/Command')->toString(),
                array_map(fn ($plugin) => $plugin->foldername, $this->getApplication()->make(PluginsService::class)->getAllPlugins(enabledOnly: true)),
            )));

        $commands = collect(Arr::flatten(session("commands")))
            ->map(fn ($path) => $this->getApplication()->getNamespace() . Str::of($path)->remove([APP_ROOT . '/app/', APP_ROOT . '/custom/'])->replace(['/', '.php'], ['\\', ''])->toString());

        /**
         * Other commands to be included
         *
         * @var LaravelCommand[]|SymfonyCommand[] $additionalCommands
         **/
        $glob = glob(APP_ROOT . '/vendor/illuminate/*/Console/*.php');
        $laravelCommands = collect($glob)->map(function ($command) {
            $path = Str::replace(APP_ROOT."/vendor/illuminate/", "", $command);
            $cleanPath = ucfirst(Str::replace(['/', '.php'], ['\\', ''], $path));
            return "Illuminate\\".$cleanPath;
        });
        session(["commands.laravel" => $laravelCommands]);

        $additionalCommands = self::dispatch_filter('additional_commands', [
            \Illuminate\Console\Scheduling\ScheduleRunCommand::class,
            \Illuminate\Console\Scheduling\ScheduleFinishCommand::class,
            \Illuminate\Console\Scheduling\ScheduleListCommand::class,
            \Illuminate\Console\Scheduling\ScheduleTestCommand::class,
            \Illuminate\Console\Scheduling\ScheduleWorkCommand::class,
            \Illuminate\Console\Scheduling\ScheduleClearCacheCommand::class,
        ]);

        $commands = collect($commands)->concat($laravelCommands);

        collect($commands)->concat($additionalCommands)
            ->each(function ($command) {
                if (
                    ! is_subclass_of($command, SymfonyCommand::class)
                    || (new \ReflectionClass($command))->isAbstract()
                ) {
                    return;
                }

                $command = $this->getApplication()->make($command);

                if ($command instanceof LaravelCommand) {
                    $command->setLaravel($this->getApplication());
                }

                $this->getArtisan()->add($command);
            });

        $commandsLoaded = true;
    }

    /**
     * Schedule tasks to be executed.
     *
     * @return void
     */
    private function schedule()
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
     * @param  \Symfony\Component\Console\Input\InputInterface        $input
     * @param  \Symfony\Component\Console\Output\OutputInterface|null $output
     * @return int
     */
    public function handle($input, $output = null)
    {
        $this->commandStartedAt = microtime(true);

        try {
            $this->bootstrap();

            return $this->getArtisan()->run($input, $output);
        } catch (\Throwable $e) {
            error_log($e);

            return 1;
        }
    }

    /**
     * Run an Artisan console command by name.
     *
     * @param  string                                                 $command
     * @param  array                                                  $parameters
     * @param  \Symfony\Component\Console\Output\OutputInterface|null $outputBuffer
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
     * @param  string $command
     * @param  array  $parameters
     * @todo Implement
     */
    /** @phpstan-ignore-next-line */
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
     * @param  \Symfony\Component\Console\Input\InputInterface $input
     * @param  int                                             $status
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
}
