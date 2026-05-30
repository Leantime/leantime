<?php

use Illuminate\Support\Facades\Route;
use Leantime\Core\Routing\ControllerDispatch;
use Leantime\Domain\Setting\Controllers\Logo;

/*
|--------------------------------------------------------------------------
| Setting Domain Routes
|--------------------------------------------------------------------------
|
| The company logo upload was relocated here from the retired Api\Controllers\Setting.
| The canonical route is /setting/logo. The /api/setting alias is kept so the existing
| company-settings logo cropper (settingRepository.js) keeps working without a JS change.
|
*/

// Canonical: /setting/logo (POST logo upload, admin+).
Route::post('/setting/logo', fn () => ControllerDispatch::dispatch(Logo::class))
    ->name('setting.logo');

// Backward-compat alias for the retired /api/setting endpoint.
Route::post('/api/setting', fn () => ControllerDispatch::dispatch(Logo::class))
    ->name('setting.logo.legacy');
