<?php

namespace Leantime\Core;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container as IlluminateContainerContract;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Redis\RedisManager;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Modulemanager\Services\Modulemanager as ModulemanagerService;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Psr\Container\ContainerInterface as PsrContainerContract;
use Symfony\Component\ErrorHandler\Debug;

/**
 * Bootloader
 *
 * @package leantime
 * @subpackage core
 */
class Bootloader
{
    use Eventhelpers;

    /**
     * Bootloader instance
     *
     * @var static
     */
    protected static ?Bootloader $instance = null;

    /**
     * Application instance
     *
     * @var Application|PsrContainerContract|null
     */
    protected Application|null|PsrContainerContract $app;

    /**
     * Public actions
     *
     * @var array
     */
    private array $publicActions = array(
        "auth.login",
        "auth.resetPw",
        "auth.userInvite",
        "install",
        "install.update",
        "errors.error403",
        "errors.error404",
        "errors.error500",
        "errors.error501",
        "api.i18n",
        "calendar.ical",
        "oidc.login",
        "oidc.callback",
        "cron.run",
    );

    /**
     * Telemetry response
     *
     * @var bool|PromiseInterface
     */
    private bool|PromiseInterface $telemetryResponse;

    /**
     * Get the Bootloader instance
     *
     * @param PsrContainerContract|null $app
     * @return Bootloader
     */
    public static function getInstance(?PsrContainerContract $app = null): self
    {

        if (is_null(static::$instance)) {
            static::$instance = new self($app);
        }

        return static::$instance;
    }

    /**
     * Constructor
     *
     * @param PsrContainerContract|null $app
     */
    private function __construct(?PsrContainerContract $app = null)
    {
        $this->app = $app;
    }

    /**
     * Boot the Application.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function __invoke(): void
    {
        //$this->boot();
    }

    /**
     * Execute the Application lifecycle.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if (! defined('LEANTIME_START')) {
            define('LEANTIME_START', microtime(true));
        }

        $this->app = new Application();

        $this->app = self::dispatch_filter("initialized", $this->app, ['bootloader' => $this]);

        self::dispatch_event("beginning", ['bootloader' => $this]);

        if ($this->app::hasBeenBootstrapped()) {
            return;
        }

        $time = microtime(true) - LEANTIME_START;

        $this->handleRequest();

        $this->app::setHasBeenBootstrapped();

        self::dispatch_event("end", ['bootloader' => $this]);
    }



    /**
     * Handle the request
     *
     * @return void
     * @throws BindingResolutionException
     * @todo Refactor into middleware and then dispatch
     */
    private function handleRequest(): void
    {
        if (! ($request = $this->app->make(IncomingRequest::class)) instanceof CliRequest) {
            /** @var HttpKernel $kernel */
            $kernel = $this->app->make(HttpKernel::class);

            $response = $kernel->handle($request)->send();

            $kernel->terminate($request, $response);
        } else {
            /** @var ConsoleKernel $kernel */
            $kernel = $this->app->make(ConsoleKernel::class);

            $status = $kernel->handle(
                $input = new \Symfony\Component\Console\Input\ArgvInput(),
                new \Symfony\Component\Console\Output\ConsoleOutput()
            );

            $kernel->terminate($input, $status);

            exit($status);
        }
    }




}
