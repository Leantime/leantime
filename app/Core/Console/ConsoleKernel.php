<?php

namespace Leantime\Core\Console;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Leantime\Core\Console\Application as LeantimeCli;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\EventDispatcher;
use Symfony\Component\Finder\Finder;

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

    public function __construct(Application $app, Dispatcher $events)
    {
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', 'bin/leantime');
        }

        parent::__construct($app, $events);
    }

    public function bootstrap()
    {

        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }

        $this->app->loadDeferredProviders();

        if (! $this->commandsLoaded) {
            $this->commands();

            if ($this->shouldDiscoverCommands()) {
                $this->discoverCommands();
            }

            $this->commandsLoaded = true;
        }
    }

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

            if ($domain = $input->getParameterOption('--domain')) {
                $this->setDomain($domain);
            }

            $this->bootstrap();

            self::dispatch_event('console.bootstrapped', ['kernel' => $this, 'command' => $input]);

            return $this->getArtisan()->run($input, $output);

        } catch (\Throwable $e) {

            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
        }
    }

    // We need to overwrite this because for some reason Laravel decided to only do command discovery if being called
    // from the original kernel
    protected function shouldDiscoverCommands()
    {
        return get_class($this) === __CLASS__;
    }

    protected function discoverCommands()
    {

        // Update standard commandPath
        $this->commandPaths = [
            APP_ROOT.'/app/Command/',
        ];

        foreach ($this->commandPaths as $path) {
            $this->load($path);
        }

        // Load Dynamic command paths for leantime
        $ltCommands = collect(glob(APP_ROOT.'/app/Domain/**/Command/') ?? []);

        // Load commands from enabled plugins
        try {
            $pluginService = app()->make(\Leantime\Core\Plugins\Plugins::class);
            $enabledPluginPaths = $pluginService->getEnabledPluginPaths();

            foreach ($enabledPluginPaths as $pluginInfo) {
                $commandPath = $pluginInfo['path'].'/Command/';

                if (is_dir($commandPath)) {

                    if ($pluginInfo['format'] == 'phar') {

                        include_once $pluginInfo['path'];
                        $this->loadPhar($commandPath, $pluginInfo['foldername'].'.phar');
                    }

                    $this->load($commandPath);
                }
            }
        } catch (\Exception $e) {
            // Fallback to scanning all plugin directories if service unavailable
            $ltPluginCommands = collect(glob(APP_ROOT.'/app/Plugins/**/Command/') ?? []);
            foreach ($ltPluginCommands as $pluginPath) {
                $this->load($pluginPath);
            }
        }

        foreach ($this->commandRoutePaths as $path) {
            if (file_exists($path)) {
                require $path;
            }
        }
    }

    public function call($command, array $parameters = [], $outputBuffer = null)
    {

        if (array_key_exists('--domain', $parameters)) {
            $this->setDomain($parameters['--domain']);
        }

        if (in_array($command, ['env:encrypt', 'env:decrypt'], true)) {
            $this->bootstrapWithoutBootingProviders();
        }

        $this->bootstrap();

        self::dispatch_event('console.bootstrapped', ['kernel' => $this, 'command' => $command]);

        return $this->getArtisan()->call($command, $parameters, $outputBuffer);
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

    protected function schedule(Schedule $schedule)
    {
        // Set default timezone
        // config(['app.timezone' => config('defaultTimezone')]);

        config(['schedule_timezone' => 'UTC']);

        self::dispatch_event('cron', ['schedule' => $schedule], 'schedule');

    }

    public function setDomain(string $domain)
    {

        if ($domain) {
            putenv('LEAN_APP_URL='.$domain);
            putenv('APP_URL='.$domain);

            // When calling commands inside the app we can switch domains
            if (isset($this->app['config'])) {
                config(['app.url' => $domain]);
                config(['appUrl' => $domain]);
            }

        }

    }

    protected function loadPhar($paths, $pharName)
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        $this->loadedPaths = array_values(
            array_unique(array_merge($this->loadedPaths, $paths))
        );

        $namespace = $this->app->getNamespace();

        foreach (Finder::create()->in($paths)->files() as $file) {

            $command = $namespace.str_replace(
                ['/', '.php', '\\'.$pharName],
                ['\\', '', ''],
                Str::after($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename(), realpath(app_path()).DIRECTORY_SEPARATOR)
            );

            if (is_subclass_of($command, Command::class) &&
                ! (new \ReflectionClass($command))->isAbstract()) {
                Artisan::starting(function ($artisan) use ($command) {
                    $artisan->resolve($command);
                });
            }
        }
    }
}
