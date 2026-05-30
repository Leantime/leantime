<?php

use Illuminate\Support\Facades\Route;
use Leantime\Domain\Blueprints\Controllers;

/*
|--------------------------------------------------------------------------
| Blueprints Domain Routes
|--------------------------------------------------------------------------
|
| Native Laravel routes bound directly to plain Blueprints controllers
| ([Controller::class, 'method']). The {canvasSlug} segment selects the
| YAML-defined canvas variant (resolved in each controller's constructor);
| the optional {id} segment is passed to the action as a typed argument.
|
| This replaces the former blueprintsDispatch() helper, which mirrored
| Frontcontroller::executeAction (verb dispatch + merged-$params + an
| $_GET['id'] superglobal injection). Laravel's router now does the verb
| dispatch (one route per verb) and the controllers read their input from
| the injected IncomingRequest.
|
*/

// Boards overview (absorbed from the former Strategy domain). Declared before the
// {canvasSlug} group so the literal "showBoards" path is never treated as a slug.
Route::get('/blueprints/showBoards', [Controllers\ShowBoards::class, 'get'])->name('blueprints.showBoards');

Route::prefix('blueprints/{canvasSlug}')->group(function () {
    Route::get('/showCanvas/{id?}', [Controllers\ShowCanvas::class, 'get'])->name('blueprints.show');
    Route::post('/showCanvas/{id?}', [Controllers\ShowCanvas::class, 'post'])->name('blueprints.show.post');

    Route::get('/editCanvasItem/{id?}', [Controllers\EditCanvasItem::class, 'get'])->name('blueprints.editItem');
    Route::post('/editCanvasItem/{id?}', [Controllers\EditCanvasItem::class, 'post'])->name('blueprints.editItem.post');

    Route::get('/editCanvasComment/{id?}', [Controllers\EditCanvasComment::class, 'get'])->name('blueprints.editComment');
    Route::post('/editCanvasComment/{id?}', [Controllers\EditCanvasComment::class, 'post'])->name('blueprints.editComment.post');

    Route::get('/boardDialog/{id?}', [Controllers\BoardDialog::class, 'get'])->name('blueprints.boardDialog');
    Route::post('/boardDialog/{id?}', [Controllers\BoardDialog::class, 'post'])->name('blueprints.boardDialog.post');

    Route::get('/delCanvas/{id?}', [Controllers\DelCanvas::class, 'get'])->name('blueprints.delCanvas');
    Route::post('/delCanvas/{id?}', [Controllers\DelCanvas::class, 'post'])->name('blueprints.delCanvas.post');

    Route::get('/delCanvasItem/{id?}', [Controllers\DelCanvasItem::class, 'get'])->name('blueprints.delCanvasItem');
    Route::post('/delCanvasItem/{id?}', [Controllers\DelCanvasItem::class, 'post'])->name('blueprints.delCanvasItem.post');

    Route::get('/export/{id?}', [Controllers\Export::class, 'get'])->name('blueprints.export');
});

// Inline item updates (status / relates / assignee) from the board view.
Route::patch('/api/blueprints/{canvasSlug}', [Controllers\ApiCanvas::class, 'patch'])->name('blueprints.api.patch');

// Legacy redirect: the former /strategy/showBoards overview now lives in Blueprints.
Route::any('/strategy/showBoards/{id?}', fn () => redirect('/blueprints/showBoards', 301));

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
