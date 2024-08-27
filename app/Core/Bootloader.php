<?php

namespace Leantime\Core;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Bootstrap\Illuminate;
use Leantime\Core\Console\CliRequest;
use Leantime\Core\Console\ConsoleKernel;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Exceptions\ExceptionHandler;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\HttpKernel;
use Leantime\Core\Http\IncomingRequest;
use Psr\Container\ContainerInterface as PsrContainerContract;

/**
 * Bootloader
 *
 * @package leantime
 * @subpackage core
 */
class Bootloader
{
    use DispatchesEvents;

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
     * Telemetry response
     *
     * @var bool|PromiseInterface
     */
    private bool|PromiseInterface $telemetryResponse;

    /**
     * The base path for the Laravel installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Get the Bootloader instance
     *
     * @param PsrContainerContract|null $app
     * @return Bootloader
     */
    public static function getInstance(string $basePath = ''): self
    {

        if (is_null(static::$instance)) {
            static::$instance = new self($basePath);
        }

        return static::$instance;
    }

    /**
     * Constructor
     *
     * @param PsrContainerContract|null $app
     */
    private function __construct(string $basePath)
    {
        $this->basePath = $basePath;
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
    public function boot(): Application
    {
        if (! defined('LEANTIME_START')) {
            define('LEANTIME_START', microtime(true));
        }

        //Start Application
        //Load the bindings and service providers
        $this->app = new Application($this->basePath);
        $this->app = self::dispatch_filter("initialized", $this->app, ['bootloader' => $this]);

        //Binding Kernels & primary exception handler
        $this->app->singleton(Illuminate\Contracts\Http\Kernel::class, HttpKernel::class);
        $this->app->singleton(Illuminate\Contracts\Console\Kernel::class, ConsoleKernel::class);
        $this->app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, ExceptionHandler::class);

        //Capture the request and instantiate the correct type
        $request = $this->bindRequest();

        //Use the right kernel for the job and handle the request.
        $this->handleRequest($request);


        //self::dispatch_event("beginning", ['bootloader' => $this]);


        //if ($this->app::hasBeenBootstrapped()) {
        //    return;
        //}

        //$time = microtime(true) - LEANTIME_START;



        //$this->app::setHasBeenBootstrapped();

        self::dispatch_event("end", ['bootloader' => $this]);

        return $this->app;
    }

    /**
     * Bind the incoming request to the application.
     *
     * This method parses the headers and creates an instance of the appropriate request class based on the headers.
     * The request class is then bound to the container as a singleton.
     *
     * @return void
     */
    public function bindRequest()
    {

        $headers = collect(getallheaders())
            ->mapWithKeys(fn ($val, $key) => [
                strtolower($key) => match (true) {
                    in_array($val, ['false', 'true']) => filter_var($val, FILTER_VALIDATE_BOOLEAN),
                    preg_match('/^[0-9]+$/', $val) => filter_var($val, FILTER_VALIDATE_INT),
                    default => $val,
                },
            ])
            ->all();


        $this->app->singleton(IncomingRequest::class, function () use ($headers) {

            $request = match (true) {
                isset($headers['hx-request']) => HtmxRequest::createFromGlobals(),
                isset($headers['x-api-key']) => ApiRequest::createFromGlobals(),
                defined('LEAN_CLI') && LEAN_CLI => CliRequest::createFromGlobals(),
                default => IncomingRequest::createFromGlobals(),
            };

            $request->overrideGlobals();
            //do_once('overrideGlobals', fn () => $request->overrideGlobals());

            return $request;

        });

        return $this->app->make(IncomingRequest::class);
    }


    /**
     * Handle the request
     *
     * @return void
     * @throws BindingResolutionException
     *
     */
    private function handleRequest(IncomingRequest $request): void
    {

        if (! ($request instanceof CliRequest)) {

            /** @var HttpKernel $kernel */
            $kernel = $this->app->make(HttpKernel::class);

            $kernelHandler = $kernel->handle($request);
            $response = $kernelHandler->send();

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
