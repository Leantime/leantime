<?php

use Illuminate\Support\Facades\Route;
use Leantime\Domain\Blueprints\Controllers;

Route::prefix('blueprints/{canvasSlug}')->group(function () {
    Route::match(['get', 'post'], '/showCanvas/{id?}', [Controllers\ShowCanvas::class, 'run'])
        ->name('blueprints.show');
    Route::match(['get', 'post'], '/editCanvasItem/{id?}', [Controllers\EditCanvasItem::class, 'dispatch'])
        ->name('blueprints.editItem');
    Route::match(['get', 'post'], '/editCanvasComment/{id?}', [Controllers\EditCanvasComment::class, 'dispatch'])
        ->name('blueprints.editComment');
    Route::match(['get', 'post'], '/boardDialog/{id?}', [Controllers\BoardDialog::class, 'run'])
        ->name('blueprints.boardDialog');
    Route::match(['get', 'post'], '/delCanvas/{id?}', [Controllers\DelCanvas::class, 'run'])
        ->name('blueprints.delCanvas');
    Route::match(['get', 'post'], '/delCanvasItem/{id?}', [Controllers\DelCanvasItem::class, 'run'])
        ->name('blueprints.delCanvasItem');
    Route::get('/export/{id?}', [Controllers\Export::class, 'run'])
        ->name('blueprints.export');
});

// API route
Route::patch('/api/blueprints/{canvasSlug}', [Controllers\ApiCanvas::class, 'patch'])
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
