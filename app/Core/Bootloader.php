<?php

namespace Leantime\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Console\ConsoleKernel;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\HttpKernel;
use Leantime\Core\Http\IncomingRequest;

/**
 * Bootloader
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

    protected Application $app;

    /**
     * Get the Bootloader instance
     *
     * @param  Application  $app
     */
    public static function getInstance(): self
    {

        if (is_null(static::$instance)) {
            static::$instance = new self;
        }

        return static::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {}

    /**
     * Execute the Application lifecycle.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function boot(Application $app)
    {
        if (! defined('LEANTIME_START')) {
            define('LEANTIME_START', microtime(true));
        }

        //Start Application
        //Load the bindings and service providers
        $this->app = $app;

        //Capture the request and instantiate the correct type
        $request = IncomingRequest::capture();


        //Use the right kernel for the job and handle the request.
        $this->handleRequest($request);

        self::dispatchEvent('end', ['bootloader' => $this]);

    }

    /**
     * Handle the request
     *
     * @throws BindingResolutionException
     */
    private function handleRequest($request): void
    {

        if (! $this->app->runningInConsole()) {

            /** @var HttpKernel $kernel */
            $kernel = $this->app->make(HttpKernel::class);

            $kernelHandler = $kernel->handle($request);
            $response = $kernelHandler->send();

            $kernel->terminate($request, $response);

        } else {

            /** @var ConsoleKernel $kernel */
            $kernel = $this->app->make(ConsoleKernel::class);

            $status = $kernel->handle(
                $input = new \Symfony\Component\Console\Input\ArgvInput,
                new \Symfony\Component\Console\Output\ConsoleOutput
            );

            $kernel->terminate($input, $status);

            exit($status);
        }
    }
}
