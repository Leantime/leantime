<?php

use Illuminate\Support\Facades\Route;
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

// Canonical
Route::get('/projects/projectImage/{id?}', [ProjectImage::class, 'show'])->name('projects.projectImage');
Route::post('/projects/projectImage', [ProjectImage::class, 'upload']);

// Backward-compat alias for the retired /api/projects avatar endpoint
Route::get('/api/projects', [ProjectImage::class, 'show'])->name('projects.projectImage.legacy');
Route::post('/api/projects', [ProjectImage::class, 'upload']);
