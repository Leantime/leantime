<?php

use Illuminate\Support\Facades\Route;
use Leantime\Core\Routing\ControllerDispatch;
use Leantime\Domain\Projects\Controllers\ProjectImage;

/*
|--------------------------------------------------------------------------
| Projects Domain Routes
|--------------------------------------------------------------------------
|
| Project avatars were relocated here from the retired Api\Controllers\Projects.
| The canonical route is /projects/projectImage/{id}. The /api/projects alias is
| kept because core templates and the plugin submodule (StrategyPro, PgmPro) hardcode
| /api/projects?projectAvatar= and cannot be rewritten from this repo. Both paths are
| served directly (no redirect) to avoid an avatar redirect-storm on project lists.
|
| The JSON sort/status/patch operations the old /api/projects controller also handled
| now go through JSON-RPC (Projects.Projects.patchProjectStatusAndSorting / sortProjects
| / patchProject), so this alias only covers the avatar GET and the avatar upload POST.
|
*/

// Canonical: /projects/projectImage/{id} (GET image, POST avatar upload).
Route::match(['get', 'post'], '/projects/projectImage/{id?}', fn () => ControllerDispatch::dispatch(ProjectImage::class))
    ->name('projects.projectImage');

// Backward-compat alias for the retired /api/projects avatar endpoint.
Route::match(['get', 'post'], '/api/projects', fn () => ControllerDispatch::dispatch(ProjectImage::class))
    ->name('projects.projectImage.legacy');
