<?php

! defined('ROOT') ? define('ROOT', __DIR__.'/../public') : '';
! defined('APP_ROOT') ? define('APP_ROOT', dirname(__DIR__, 1)) : '';

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Leantime\Core\Application(APP_ROOT);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    Leantime\Core\Http\HttpKernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    Leantime\Core\Console\ConsoleKernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Leantime\Core\Exceptions\ExceptionHandler::class,
);

$app->singleton(
    Illuminate\Http\Request::class,
    \Leantime\Core\Http\IncomingRequest::class,
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;

// return Application::configure(basePath: dirname(__DIR__))
//    ->withRouting(
//        web: __DIR__.'/../routes/web.php',
//        commands: __DIR__.'/../routes/console.php',
//        health: '/up',
//    )
//    ->withMiddleware(function (Middleware $middleware) {
//        //
//    })
//    ->withExceptions(function (Exceptions $exceptions) {
//        //
//    })->create();;
