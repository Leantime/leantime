<?php

namespace Leantime\Core\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Api\Controllers\Jsonrpc;
use Symfony\Component\HttpFoundation\Response;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = 'dashboard/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (IncomingRequest $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {

            $frontController = app()->make(\Leantime\Core\Controller\Frontcontroller::class);

            Route::middleware(['api'])
                ->prefix('api')
                ->any('api/jsonrpc', function (IncomingRequest $request) use ($frontController) {

                    $httpMethod = Str::lower($request->getMethod());
                    return $frontController->executeAction(Jsonrpc::class, $httpMethod);
                });


            Route::middleware(['hx'])
                ->prefix('hx')
                ->group(function() use ($frontController) {

                    Route::any('{moduleName}/{actionName}/{methodName}', function (IncomingRequest $request, $moduleName, $actionName, $methodName) use ($frontController) {
                        $controllerParts = $frontController->getValidControllerCall($moduleName, $actionName, $methodName, "Hxcontrollers");
                        return $frontController->executeAction($controllerParts['class'], $controllerParts['method']);
                    });

                    Route::any('{moduleName}/{actionName}', function (IncomingRequest $request, $moduleName, $actionName) use ($frontController) {
                        $httpMethod = Str::lower($request->getMethod());
                        $controllerParts = $frontController->getValidControllerCall($moduleName, $actionName, $httpMethod, "Hxcontrollers");
                        return $frontController->executeAction($controllerParts['class'], $httpMethod);
                    });

                });


            Route::middleware(['web'])->group(function() use ($frontController)  {

                Route::any('{moduleName}/{actionName}/{id}', function (IncomingRequest $request, $moduleName, $actionName, $id) use ($frontController) {
                  
                    $httpMethod = Str::lower($request->getMethod());
                    $controllerParts = $frontController->getValidControllerCall($moduleName, $actionName, $httpMethod, "Controllers");
                    $request->query->set('id', $id);
                    return $frontController->executeAction($controllerParts['class'], $controllerParts['method']);
                });

                Route::any('{moduleName}/{actionName}/{methodName}', function (IncomingRequest $request, $moduleName, $actionName, $methodName) use ($frontController)  {
                    
                    $controllerParts = $frontController->getValidControllerCall($moduleName, $actionName, $methodName, "Controllers");
                    return $frontController->executeAction($controllerParts['class'], $controllerParts['method']);
                });

                Route::any('{moduleName}/{actionName}', function (IncomingRequest $request, $moduleName, $actionName) use ($frontController)  {
                    
                    
                    $httpMethod = Str::lower($request->getMethod());
                    $controllerParts = $frontController->getValidControllerCall($moduleName, $actionName, $httpMethod, "Controllers");
                    return $frontController->executeAction($controllerParts['class'], $controllerParts['method']);
                });

                Route::any('{moduleName}', function (IncomingRequest $request, $moduleName) use ($frontController)  {
                    $httpMethod = Str::lower($request->getMethod());
                    $controllerParts = $frontController->getValidControllerCall($moduleName, "index", $httpMethod, "Controllers");
                    return $frontController->executeAction($controllerParts['class'], $controllerParts['method']);
                });

            });

            Route::any('', function (IncomingRequest $request) use ($frontController) {
                
               
                return $frontController->redirect(self::HOME);
            });
        });
    }
}
