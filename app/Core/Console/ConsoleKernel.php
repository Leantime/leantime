<?php

namespace Leantime\Core\Console;

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

    /**
     * Handle an incoming console command.
     *
     * @return int
     */
    public function handle($input, $output = null)
    {

        $this->commandStartedAt = Carbon::now();

        //        // Process global subdomain option if present
        //        if ($input->hasParameterOption('--subdomain')) {
        //            $subdomain = $input->getParameterOption('--subdomain');
        //            if ($subdomain) {
        //                session(['subdomain' => $subdomain]);
        //                session(['configSubdomain' => $subdomain]);
        //            }
        //        }

        try {

            $domain = $input->getParameterOption('--domain');
            $this->setDomain($domain);

            if (in_array($input->getFirstArgument(), ['env:encrypt', 'env:decrypt'], true)) {
                $this->bootstrapWithoutBootingProviders();
            }

            $this->bootstrap();

            self::dispatch_event('console.bootstrapped', ['kernel' => $this, 'command' => $input]);

            $cli = $this->getArtisan();
            $output = $cli->run($input, $output);

            return $output;

        } catch (\Throwable $e) {

            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
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

        //        if(isset($parameters['subdomain'])) {
        //            session(["subdomain" => $parameters['subdomain']]);
        //            unset($parameters['subdomain']);
        //        }

        self::dispatch_event('console.bootstrapped', ['kernel' => $this, 'command' => $command]);

        return $this->getArtisan()->call($command, $parameters, $outputBuffer);
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

        // $customCommands = $customPluginCommands = null;

        $ltCommands = collect(glob(APP_ROOT.'/app/Domain/**/Command/') ?? []);
        $ltPluginCommands = collect(glob(APP_ROOT.'/app/Plugins/**/Command/') ?? []);
        /*$commands = collect(Arr::flatten($ltCommands))
            ->map(fn ($path) => $this->getApplication()->getNamespace().Str::of($path)->remove([APP_ROOT.'/app/', APP_ROOT.'/Custom/'])->replace(['/', '.php'], ['\\', ''])->toString());
        */

        $this->load(APP_ROOT.'/app/Command/');
        $this->load(APP_ROOT.'/app/Domain/**/Command/');

        foreach ($ltPluginCommands as $pluginPath) {
            $this->load($pluginPath);
        }

        $commandsLoaded = true;
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

        // $this->bindSchedule();

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
        // Set default timezone
        config(['app.timezone' => config('defaultTimezone')]);

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
}
