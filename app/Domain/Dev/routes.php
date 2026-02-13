<?php

use Illuminate\Support\Facades\Route;
use Leantime\Domain\Dev\Controllers\ComponentPreview;

// Only register dev routes when debug mode is enabled
if ((bool) config('app.debug') === true) {
    Route::get('/dev/components', [ComponentPreview::class, 'get'])
        ->name('dev.components');
}
