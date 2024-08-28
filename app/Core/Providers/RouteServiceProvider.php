<?php

namespace Leantime\Core\Providers;

use Closure;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * @mixin \Illuminate\Routing\Router
 */
class RouteServiceProvider extends ServiceProvider
{
    use ForwardsCalls;

    /**
     * The controller namespace for the application.
     *
     * @var string|null
     */
    protected $namespace;

    /**
     * The callback that should be used to load the application's routes.
     *
     * @var \Closure|null
     */
    protected $loadRoutesUsing;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->booted(function () {
            $this->setRootControllerNamespace();

            if ($this->routesAreCached()) {
                $this->loadCachedRoutes();
            } else {
                $this->loadRoutes();

                $this->app->booted(function () {
                    $this->app['router']->getRoutes()->refreshNameLookups();
                    $this->app['router']->getRoutes()->refreshActionLookups();
                });
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->routes(function () {
            Route::any(
                '{controller_name}/{function_name}/{query1?}/{query2?}/{query3?}',
                function ($module_name, $action_name, IncomingRequest $request, ?string $query1 = null) {
                       return app('frontcontroller')->dispatch();
                }
            );
        });
    }

    /**
     * Register the callback that will be used to load the application's routes.
     *
     * @param  \Closure  $routesCallback
     * @return $this
     */
    protected function routes(Closure $routesCallback)
    {
        $this->loadRoutesUsing = $routesCallback;

        return $this;
    }

    /**
     * Set the root controller namespace for the application.
     *
     * @return void
     */
    protected function setRootControllerNamespace()
    {
        if (! is_null($this->namespace)) {
            $this->app[UrlGenerator::class]->setRootControllerNamespace($this->namespace);
        }
    }

    /**
     * Determine if the application routes are cached.
     *
     * @return bool
     */
    protected function routesAreCached()
    {
        return $this->app->routesAreCached();
    }

    /**
     * Load the cached routes for the application.
     *
     * @return void
     */
    protected function loadCachedRoutes()
    {
        $this->app->booted(function () {
            require $this->app->getCachedRoutesPath();
        });
    }

    /**
     * Load the application routes.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        if (! is_null($this->loadRoutesUsing)) {
            $this->app->call($this->loadRoutesUsing);
        } elseif (method_exists($this, 'map')) {
            $this->app->call([$this, 'map']);
        }
    }

    private function executeAction(string $module, string $action, IncomingRequest $request, $query1 = null, $query2 = null)
    {
        $namespace = app()->getNamespace(false);
        $actionName = Str::studly($action);
        $moduleName = Str::studly($module);

        $this->app['events']->dispatch_event("execute_action_start", ["action"=>$actionName, "module"=>$moduleName ]);

        $controllerNs = "Domain";
        $controllerType = $request instanceof HtmxRequest ? 'Hxcontrollers' : 'Controllers';
        $classname = $namespace."".$controllerNs."\\".$moduleName."\\".$controllerType."\\".$actionName;

        if (! class_exists($classname)) {

            $classname = $namespace."Plugins\\".$moduleName."\\".$controllerType."\\".$actionName;

            $enabledPlugins = app()->make(\Leantime\Domain\Plugins\Services\Plugins::class)->getEnabledPlugins();

            $pluginEnabled = false;
            foreach ($enabledPlugins as $key => $obj) {
                if (strtolower($obj->foldername) !== strtolower($moduleName)) {
                    continue;
                }
                $pluginEnabled = true;
                break;
            }

            if (! $pluginEnabled || ! class_exists($classname)) {
                return $controllerType == 'Hxcontrollers' ? new Response('', 404) : $this->redirect(BASE_URL . "/errors/error404", 307);
            }
        }

        $this->lastAction = $moduleName.".".$actionName;

        $this->app['events']->dispatch_event("execute_action_end", ["action"=>$actionName, "module"=>$moduleName ]);

        return app()->make($classname)->getResponse();
    }

    /**
     * Pass dynamic methods onto the router instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo(
            $this->app->make(Router::class), $method, $parameters
        );
    }
}
