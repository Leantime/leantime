<?php

use Illuminate\Support\Facades\Route;
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

// Canonical
Route::post('/setting/logo', [Logo::class, 'post'])->name('setting.logo');

// Backward-compat alias for the retired /api/setting endpoint
Route::post('/api/setting', [Logo::class, 'post'])->name('setting.logo.legacy');
