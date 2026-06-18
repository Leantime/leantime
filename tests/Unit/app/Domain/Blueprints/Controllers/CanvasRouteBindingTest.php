<?php

namespace Unit\app\Domain\Blueprints\Controllers;

use Illuminate\Routing\RouteDependencyResolverTrait;
use Leantime\Core\Http\IncomingRequest;
use ReflectionMethod;
use Unit\TestCase;

/**
 * Regression coverage for canvas modal actions 404ing (PR #3544).
 *
 * The Blueprints controllers are routed under `blueprints/{canvasSlug}/{action}/{id?}`.
 * Their action methods were declared `get(?string $id = null)` — omitting the
 * `{canvasSlug}` segment — so Laravel bound the FIRST route param (`canvasSlug`) to
 * the first method param (`$id`). `$id` then held the slug ("swot"), the real id was
 * dropped, and EditCanvasItem/EditCanvasComment rendered the `errors.error404` partial
 * (at HTTP 200) for every add/edit. The fix declares `$canvasSlug` first so `$id`
 * binds to the real id. These tests assert that binding via the real route table —
 * no DB/session/browser needed.
 */
class CanvasRouteBindingTest extends TestCase
{
    /** Routed Blueprints actions that take an {id?} segment, by action name. */
    public static function canvasActionProvider(): array
    {
        return [
            'showCanvas' => ['showCanvas'],
            'editCanvasItem' => ['editCanvasItem'],
            'editCanvasComment' => ['editCanvasComment'],
            'boardDialog' => ['boardDialog'],
            'delCanvas' => ['delCanvas'],
            'delCanvasItem' => ['delCanvasItem'],
            'export' => ['export'],
        ];
    }

    /**
     * Register the real Blueprints routes into the current app's router. Uses the
     * actual routes.php (not a hand-rolled copy) so the test tracks the real
     * definitions. Re-required per test because each test gets a fresh app/router.
     */
    private function matchRoute(string $uri): \Illuminate\Routing\Route
    {
        require APP_ROOT.'/app/Domain/Blueprints/routes.php';

        $request = IncomingRequest::create($uri, 'GET');
        $route = $this->app->make('router')->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);

        return $route;
    }

    /**
     * Map the arguments the controller action actually RECEIVES for $route, modelling
     * exactly what Illuminate\Routing\ControllerDispatcher::dispatch() does:
     *
     *     $controller->{$method}(...array_values($resolvedParameters));
     *
     * The values are spread POSITIONALLY, so what matters is each method parameter's
     * position, not the route key. This is the layer the bug lived in: with
     * `get(?string $id)` the slug (first positional value) landed in `$id`. Returns a
     * [paramName => boundValue] map.
     */
    private function actionReceives(\Illuminate\Routing\Route $route): array
    {
        $resolver = new class($this->app)
        {
            use RouteDependencyResolverTrait;

            public function __construct(public $container) {}

            public function resolve($route): array
            {
                return $this->resolveClassMethodDependencies(
                    $route->parametersWithoutNulls(),
                    $route->getControllerClass(),
                    $route->getActionMethod(),
                );
            }
        };

        $positional = array_values($resolver->resolve($route));
        $params = (new ReflectionMethod($route->getControllerClass(), $route->getActionMethod()))->getParameters();

        $bound = [];
        foreach ($params as $i => $param) {
            $bound[$param->getName()] = $positional[$i] ?? null;
        }

        return $bound;
    }

    /**
     * @dataProvider canvasActionProvider
     */
    public function test_route_binds_real_id_not_canvas_slug(string $action): void
    {
        $received = $this->actionReceives($this->matchRoute("/blueprints/swot/{$action}/42"));

        $this->assertSame('swot', $received['canvasSlug'] ?? null, "{$action}: \$canvasSlug must receive the slug");
        $this->assertSame('42', $received['id'] ?? null, "{$action}: \$id must receive the route id, not the slug");
    }

    /**
     * @dataProvider canvasActionProvider
     */
    public function test_missing_id_does_not_leak_slug_into_id(string $action): void
    {
        $received = $this->actionReceives($this->matchRoute("/blueprints/swot/{$action}"));

        $this->assertSame('swot', $received['canvasSlug'] ?? null, "{$action}: \$canvasSlug must receive the slug");
        $this->assertNull($received['id'] ?? null, "{$action}: omitted {id?} must not leak the slug into \$id");
    }

    /**
     * The structural invariant behind the fix: any Blueprints action routed under the
     * {canvasSlug} prefix must declare `canvasSlug` as its first parameter, so route
     * params line up with method params. Guards against re-introducing the bug on a
     * new action.
     *
     * @dataProvider canvasActionProvider
     */
    public function test_action_declares_canvas_slug_as_first_parameter(string $action): void
    {
        $route = $this->matchRoute("/blueprints/swot/{$action}");

        $params = (new ReflectionMethod($route->getControllerClass(), $route->getActionMethod()))->getParameters();

        $this->assertNotEmpty($params, "{$action}: action must declare parameters");
        $this->assertSame('canvasSlug', $params[0]->getName(), "{$action}: first parameter must be \$canvasSlug");
    }
}
