<?php

use Illuminate\Support\Facades\Route;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Blueprints\Controllers;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| Blueprints Domain Routes
|--------------------------------------------------------------------------
|
| The Blueprints controllers are written for the legacy Frontcontroller
| invocation convention: they read $_GET['id'], expect the request body merged
| into the $params argument, dispatch by HTTP verb, and may return a string
| fragment instead of a Response. The Frontcontroller cannot parse the
| /blueprints/{slug}/{action}/{id} URL shape because it needs a dedicated
| {canvasSlug} segment, so we register explicit Laravel routes here and bridge
| each one into the controller exactly the way Frontcontroller::executeAction
| does. Without this bridge the {id} path segment never reaches $_GET['id'],
| POST/PATCH bodies never reach $params, and string fragments are not wrapped.
|
*/

if (! function_exists('blueprintsDispatch')) {
    /**
     * Invoke a Blueprints controller the same way Frontcontroller::executeAction does.
     *
     * @param  string  $controllerClass  Fully-qualified controller class name
     */
    function blueprintsDispatch(string $controllerClass): Response
    {
        /** @var IncomingRequest $request */
        $request = app(IncomingRequest::class);
        $route = $request->route();

        // The numeric {id} path segment is how the controllers and blades receive
        // the board/item id. Mirror Frontcontroller: push it into the query bag and
        // re-sync the PHP superglobals so $_GET['id'] and getRequestParams() see it.
        $id = $route?->parameter('id');
        if ($id !== null && $id !== '') {
            $request->query->set('id', $id);
            $request->overrideGlobals();
        }

        $controller = app()->make($controllerClass);

        // Resolve the action method the way Frontcontroller does: prefer the HTTP
        // verb (get/post/patch/delete), fall back to run() for single-method controllers.
        $verb = strtolower($request->getMethod());
        if ($verb === 'head') {
            $verb = 'get';
        }
        $method = method_exists($controller, $verb) ? $verb : 'run';

        $params = $request->getRequestParams();
        $response = $controller->callAction($method, $params);

        // get()/post()/run() may return a Response directly or a rendered string;
        // wrap the latter via the controller's stored response.
        return $response instanceof Response ? $response : $controller->getResponse();
    }
}

Route::prefix('blueprints/{canvasSlug}')->group(function () {
    Route::match(['get', 'post'], '/showCanvas/{id?}', fn () => blueprintsDispatch(Controllers\ShowCanvas::class))
        ->name('blueprints.show');
    Route::match(['get', 'post'], '/editCanvasItem/{id?}', fn () => blueprintsDispatch(Controllers\EditCanvasItem::class))
        ->name('blueprints.editItem');
    Route::match(['get', 'post'], '/editCanvasComment/{id?}', fn () => blueprintsDispatch(Controllers\EditCanvasComment::class))
        ->name('blueprints.editComment');
    Route::match(['get', 'post'], '/boardDialog/{id?}', fn () => blueprintsDispatch(Controllers\BoardDialog::class))
        ->name('blueprints.boardDialog');
    Route::match(['get', 'post'], '/delCanvas/{id?}', fn () => blueprintsDispatch(Controllers\DelCanvas::class))
        ->name('blueprints.delCanvas');
    Route::match(['get', 'post'], '/delCanvasItem/{id?}', fn () => blueprintsDispatch(Controllers\DelCanvasItem::class))
        ->name('blueprints.delCanvasItem');
    Route::get('/export/{id?}', fn () => blueprintsDispatch(Controllers\Export::class))
        ->name('blueprints.export');
});

// Inline item updates (status / relates / assignee) from the board view.
Route::patch('/api/blueprints/{canvasSlug}', fn () => blueprintsDispatch(Controllers\ApiCanvas::class))
    ->name('blueprints.api.patch');

// Legacy redirects: forward old /xxxcanvas/ URLs to /blueprints/xxx/
$legacySlugs = ['swot', 'lean', 'cp', 'dbm', 'ea', 'em', 'insights', 'lbm', 'minempathy', 'obm', 'retros', 'risks', 'sb', 'sm', 'sq', 'value'];
foreach ($legacySlugs as $slug) {
    Route::any("/{$slug}canvas/{action?}/{id?}", function (string $action = 'showCanvas', ?string $id = null) use ($slug) {
        $path = "/blueprints/{$slug}/{$action}";
        if ($id) {
            $path .= "/{$id}";
        }
        $queryString = request()->getQueryString();

        return redirect($path.($queryString ? '?'.$queryString : ''), 301);
    })->where('action', '[a-zA-Z]+');
}
