<?php

use Illuminate\Support\Facades\Route;
use Leantime\Core\Routing\ControllerDispatch;
use Leantime\Domain\Users\Controllers\ProfileImage;

/*
|--------------------------------------------------------------------------
| Users Domain Routes
|--------------------------------------------------------------------------
|
| Profile images were relocated here from the retired Api\Controllers\Users.
| The canonical route is /users/profileImage/{id}. The /api/users alias is kept
| because ~70 <img src> references across core templates AND the plugin submodule
| (Copilot, StrategyPro, PgmPro, Whiteboardscanvas, Llamadorian) hardcode
| /api/users?profileImage= and cannot be rewritten from this repo. Both paths are
| served directly (no redirect) to avoid an avatar redirect-storm on list views.
|
*/

// Canonical: /users/profileImage/{id} (GET image, POST own-photo upload).
Route::match(['get', 'post'], '/users/profileImage/{id?}', fn () => ControllerDispatch::dispatch(ProfileImage::class))
    ->name('users.profileImage');

// Backward-compat alias for the retired /api/users endpoint.
Route::match(['get', 'post'], '/api/users', fn () => ControllerDispatch::dispatch(ProfileImage::class))
    ->name('users.profileImage.legacy');
