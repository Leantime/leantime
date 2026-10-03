<?php

use Illuminate\Support\Facades\Route;
use Leantime\Domain\Status\Controllers\Health;

/*
|--------------------------------------------------------------------------
| Status Domain Routes
|--------------------------------------------------------------------------
|
| /health is the container/uptime probe. HttpKernel::isHealthCheck() dispatches it straight to
| the router, skipping the middleware pipeline (session, install/update redirects, auth, rate
| limiting), so it must stay side-effect free and leak nothing.
|
*/

Route::get('/health', [Health::class, 'get'])->name('status.health');
